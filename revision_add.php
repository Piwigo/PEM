<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2013 PEM Team - http://piwigo.org                  |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

if (!defined('INTERNAL'))
{
  define('INTERNAL', true);
}
$root_path = './';
require_once($root_path.'include/common.inc.php');
  
if (!isset($user['id']))
{
  message_die('You must be connected to reach this page.');
}

$tpl->set_filenames(
  array(
    'page' => 'page.tpl',
    'revision_add' => 'revision_add.tpl'
  )
);

// We need a valid extension
if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_mod.php')
{
  $revision_infos_of = get_revision_infos_of(array($page['revision_id']));
  
  $page['extension_id'] =
    $revision_infos_of[ $page['revision_id'] ]['idx_extension'];
}
else
{
  $page['extension_id'] =
    (isset($_GET['eid']) and is_numeric($_GET['eid']))
    ? $_GET['eid']
    : '';
}

if (empty($page['extension_id']))
{
  message_die('Incorrect extension identifier');
}

$query = '
SELECT
    name,
    idx_user,
    svn_url,
    git_url,
    archive_root_dir,
    archive_name
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
$result = $db->query($query);

if ($db->num_rows($result) == 0)
{
  message_die('Unknown extension');
}
list($page['extension_name'], $ext_user, $svn_url, $git_url, $archive_root_dir, $archive_name) = $db->fetch_array($result);

$authors = get_extension_authors($page['extension_id']);

if (!in_array($user['id'], $authors) and !isAdmin($user['id']) and !isTranslator($user['id']))
{
  message_die('You must be the extension author to modify it.');
}

// +-----------------------------------------------------------------------+
// |                           Form submission                             |
// +-----------------------------------------------------------------------+

if (isset($_POST['submit']))
{
  // Form submitted for translator
  if (!in_array($user['id'], $authors) and !isAdmin($user['id']))
  {
    $query = 'SELECT idx_language FROM '.REV_TABLE.' WHERE id_revision = '.$page['revision_id'].';';
    $result = $db->query($query);
    list($def_language) = mysql_fetch_array($result);

    $query = '
DELETE
  FROM '.REV_TRANS_TABLE.'
  WHERE idx_revision = '.$page['revision_id'].'
    AND idx_language IN ('.implode(',', $conf['translator_users'][$user['id']]).')
;';
    $db->query($query);

    $inserts = array();
    $new_default_desc = null;
    foreach ($_POST['revision_descriptions'] as $lang_id => $desc)
    {
      if ($lang_id == $def_language and empty($desc))
      {
        $page['errors'][] = l10n('Default description can not be empty');
        break;
      }
      if (!in_array($lang_id, $conf['translator_users'][$user['id']]) or empty($desc))
      {
        continue;
      }
      if ($lang_id == $def_language)
      {
        $new_default_lang = $db->escape($desc);
      }
      else
      {
        array_push(
          $inserts,
          array(
            'idx_revision'  => $page['revision_id'],
            'idx_language'   => $lang_id,
            'description'    => $db->escape($desc),
            )
          );
      }
    }
    
    if (empty($page['errors']))
    {
      if (!empty($inserts))
      {
        mass_inserts(REV_TRANS_TABLE, array_keys($inserts[0]), $inserts);
      }
      if (!empty($new_default_desc))
      {
        $query = '
UPDATE '.REV_TABLE.'
  SET description = \''.$new_default_desc.'\'
  WHERE id_revision = '.$page['revision_id'].'
;';
        $db->query($query);
      }
    
      message_success(
        'Revision successfuly added. Thank you.',
        sprintf(
          'extension_view.php?eid=%u&amp;rid=%u#rev%u',
          $page['extension_id'],
          $page['revision_id'],
          $page['revision_id']
          )
        );
        
      unset($_POST);
    }
  }
  // The file is mandatory only when we add a revision, not when we modify it
  $file_to_upload = null;
  if ('revision_add.php' == basename($_SERVER['SCRIPT_FILENAME']))
  {
    if (isset($_POST['file_type']) and in_array($_POST['file_type'], array('upload', 'svn', 'git', 'url')))
    {
      $file_to_upload = $_POST['file_type'];
    }
    else
    {
      $page['errors'][] = l10n('Some fields are missing');
    }
  }
  else
  {
    // we are on revision_mod.php
    $file_to_upload = 'none';
  }

  if ($file_to_upload == 'upload')
  {
    // Check file extension
    $file_ext = pathinfo($_FILES['revision_file']['name'], PATHINFO_EXTENSION);
    $allowed_extensions = array('zip', 'jar');
    if (!in_array($file_ext, $allowed_extensions))
    {
      $page['errors'][] = l10n('Only *.{'.implode(', ', $allowed_extensions).'} files are allowed');
    }
  
    // Check file size
    else if ($_FILES['revision_file']['error'] == UPLOAD_ERR_INI_SIZE)
    {
      $page['errors'][] = sprintf(
        l10n('File too big. Filesize must not exceed %s.'),
        ini_get('upload_max_filesize')
        );
    }
    else
    {
      $archive_name = $_FILES['revision_file']['name'];
    }
  }

  if ($file_to_upload == 'svn')
  {
    $svn_url = $_POST['svn_url'];
    if (empty($svn_url))
    {
      $page['errors'][] = l10n('Some fields are missing');
    }
    else
    {
      $temp_path = $conf['local_data_dir'] . '/svn_import';
      if (!is_dir($temp_path))
      {
        umask(0000);
        if (!mkdir($temp_path, 0777))
        {
          die("problem during ".$temp_path." creation");
        }
      }

      // Create random path
      $temp_path .= '/' . md5(uniqid(rand(), true));

      // SVN export
      $svn_command = $conf['svn_path'] . ' export';
      $svn_command .= is_numeric($_POST['svn_revision']) ? ' -r'.$_POST['svn_revision'] : '';
      $svn_command .= ' ' . escapeshellarg($svn_url);
      $svn_command .= ' ' . $temp_path;

      exec($svn_command, $svn_infos);

      if (empty($svn_infos))
      {
        $page['errors'][] = l10n('An error occured during SVN/Git export.');
      }
      else
      {
        $archive_name = str_replace('%', @$_POST['revision_version'], $archive_name);
        $svn_revision = preg_replace('/exported revision (\d+)\./i', '$1', end($svn_infos));

        if (!empty($conf['archive_comment']) and !file_exists($temp_path.'/'.$conf['archive_comment_filename']))
        {
          file_put_contents(
            $temp_path.'/'.$conf['archive_comment_filename'],
            sprintf($conf['archive_comment'], $svn_url, $svn_revision)
          );
        }
      }
    }
  }

  if ($file_to_upload == 'git')
  {
    $git_url = $_POST['git_url'];
    if (empty($git_url))
    {
      $page['errors'][] = l10n('Some fields are missing');
    }
    else
    {
      $temp_path = $conf['local_data_dir'] . '/git_clone';
      if (!is_dir($temp_path))
      {
        umask(0000);
        if (!mkdir($temp_path, 0777))
        {
          die("problem during ".$temp_path." creation");
        }
      }

      // Create random path
      $temp_path .= '/' . md5(uniqid(rand(), true));

      // SVN export
      $git_command = $conf['git_path'] . ' clone --depth=1';
      
      if (isset($_POST['git_branch']) and 'master' != $_POST['git_branch'])
      {
        $git_command .= ' -b '.escapeshellarg($_POST['git_branch']);
      }
      
      $git_command .= ' ' . escapeshellarg($git_url);
      $git_command .= ' ' . $temp_path;

      exec($git_command, $git_infos);

      if (!file_exists($temp_path.'/.git'))
      {
        $page['errors'][] = l10n('An error occured during SVN/Git export.');
      }
      else
      {
        $archive_name = str_replace('%', @$_POST['revision_version'], $archive_name);

        unset($git_infos);
        
        $working_dir = getcwd();
        chdir($temp_path);
        $git_command = $conf['git_path'].' log ';
        exec($git_command, $git_infos);
        chdir($working_dir);

        exec('rm -rf '.$temp_path.'/.git');

        $git_commit = '';
        $git_date = '';
        foreach ($git_infos as $line)
        {
          $line = trim($line);
          if (preg_match('/commit\s+([a-f0-9]{40})/', $line, $matches))
          {
            $git_commit = $matches[1];
          }

          if (preg_match('/Date:\s*(.*)$/', $line, $matches))
          {
            $git_date = $matches[1];
          }
        }
        
        $revision = $git_commit.' ('.$git_date.')';

        if (!empty($conf['archive_comment']) and !file_exists($temp_path.'/'.$conf['archive_comment_filename']))
        {
          file_put_contents(
            $temp_path.'/'.$conf['archive_comment_filename'],
            sprintf($conf['archive_comment'], $git_url, $revision)
          );
        }
      }
    }
  }

  if ('url' == $file_to_upload)
  {
    $download_url = $_POST['download_url'];
    if (empty($download_url))
    {
      $page['errors'][] = l10n('Some fields are missing');
    }
    else
    {
      $sch = parse_url($download_url, PHP_URL_SCHEME);
      if (!in_array($sch, array('http', 'https')))
      {
        $page['errors'][] = l10n('The download URL must start with "http"');
      }
      else
      {
        $headers = get_headers($download_url, 1);
        if ($headers["Content-Length"] > $conf['download_url_max_filesize']*1024*1024)
        {
          $page['errors'][] = l10n('The archive on the download URL is bigger than '.$conf['download_url_max_filesize'].'MB');
        }
        else
        {
          $archive_name = basename($download_url);
        }
      }
    }
  }

  $required_fields = array(
    'revision_version',
    'compatible_versions',
    );
  
  foreach ($required_fields as $field)
  {
    if (empty($_POST[$field]))
    {
      @deltree($temp_path);
      $page['errors'][] = l10n('Some fields are missing');
      break;
    }
  }
  if (empty($_POST['revision_descriptions'][@$_POST['default_description']]))
  {
    @deltree($temp_path);
    $page['errors'][] = l10n('Default description can not be empty');
  }
  
  if (empty($page['errors']))
  {
    if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_mod.php')
    {
      mass_updates(
        REV_TABLE,
        array(
          'primary' => array('id_revision'),
          'update'  => array('version', 'description', 'idx_language', 'author'),
          ),
        array(
          array(
            'id_revision'    => $page['revision_id'],
            'version'        => $db->escape($_POST['revision_version']),
            'description'    => $db->escape($_POST['revision_descriptions'][$_POST['default_description']]),
            'idx_language'   => $db->escape($_POST['default_description']),
            'author'         => isset($_POST['author']) ? $db->escape($_POST['author']) : $revision_infos_of[$page['revision_id']]['author'],
            ),
          )
        );
      $query = '
DELETE
  FROM '.REV_TRANS_TABLE.'
  WHERE idx_revision = '.$page['revision_id'].'
;';
      $db->query($query);
    }
    else
    {
      $insert = array(
        'version'        => $db->escape($_POST['revision_version']),
        'idx_extension'  => $page['extension_id'],
        'date'           => mktime(),
        'description'    => $db->escape($_POST['revision_descriptions'][$_POST['default_description']]),
        'idx_language'   => $db->escape($_POST['default_description']),
        'url'            => $archive_name,
        'author'         => isset($_POST['author']) ? $db->escape($_POST['author']) : $user['id'],
        );

      if ($conf['use_agreement'])
      {
        $insert['accept_agreement'] = isset($_POST['accept_agreement'])
          ? 'true'
          : 'false'
          ;
      }
      
      mass_inserts(
        REV_TABLE,
        array_keys($insert),
        array($insert)
        );

      $page['revision_id'] = $db->insert_id();
    }

    if ($file_to_upload != 'none')
    {
      // Moves the file to its final destination:
      // upload/extension-X/revision-Y
      $extension_dir = $conf['upload_dir'].'extension-'.$page['extension_id'];
      $revision_dir = $extension_dir.'/revision-'.$page['revision_id'];
      
      if (!is_dir($extension_dir))
      {
        umask(0000);
        if (!mkdir($extension_dir, 0777, true))
        {
          die("problem during ".$extension_dir." creation");
        }
      }
      
      umask(0000);
      @mkdir($revision_dir, 0777);

      if ($file_to_upload == 'upload')
      {
        move_uploaded_file(
          $_FILES['revision_file']['tmp_name'],
          $revision_dir.'/'.$_FILES['revision_file']['name']
        );
      }
      elseif (in_array($file_to_upload, array('svn', 'git')))
      {
        // Create zip archive
        include_once($root_path.'include/pclzip.lib.php');
        $zip = new PclZip($revision_dir.'/'.$archive_name);
        $zip->create($temp_path,
          PCLZIP_OPT_REMOVE_PATH, $temp_path,
          PCLZIP_OPT_ADD_PATH, $archive_root_dir);

        @deltree($temp_path);
      }
      elseif ('url' == $file_to_upload)
      {
        copy($download_url, $revision_dir.'/'.$archive_name);
      }
    }

    // Insert translations
    $inserts = array();
    foreach ($_POST['revision_descriptions'] as $lang_id => $desc)
    {
      if ($lang_id == $_POST['default_description'] or empty($desc))
      {
        continue;
      }
      array_push(
        $inserts,
        array(
          'idx_revision'  => $page['revision_id'],
          'idx_language'   => $lang_id,
          'description'    => $db->escape($desc),
          )
        );
    }
    if (!empty($inserts))
    {
      mass_inserts(REV_TRANS_TABLE, array_keys($inserts[0]), $inserts);
    }

    $query = '
  DELETE
    FROM '.COMP_TABLE.'
    WHERE idx_revision = '.$page['revision_id'].'
  ;';
    $db->query($query);
    
    // Inserts the revisions <-> compatibilities link
    $inserts = array();
    foreach ($_POST['compatible_versions'] as $version_id)
    {
      array_push(
        $inserts,
        array(
          'idx_revision'  => $page['revision_id'],
          'idx_version'   => $db->escape($version_id),
          )
        );
    }
    mass_inserts(
      COMP_TABLE,
      array_keys($inserts[0]),
      $inserts
      );

    $query = '
DELETE
  FROM '.REV_LANG_TABLE.'
  WHERE idx_revision = '.$page['revision_id'].'
;';
    $db->query($query);

    // Inserts the revisions <-> languages
    $inserts = array();
    if (!empty($_POST['extensions_languages']))
    {
      foreach ($_POST['extensions_languages'] as $language_id)
      {
        array_push(
          $inserts,
          array(
            'idx_revision'  => $page['revision_id'],
            'idx_language'  => $db->escape($language_id),
            )
          );
      }
      mass_inserts(
        REV_LANG_TABLE,
        array_keys($inserts[0]),
        $inserts
        );
    }

    message_success(
      'Revision successfuly added. Thank you.',
      sprintf(
        'extension_view.php?eid=%u&amp;rid=%u#rev%u',
        $page['extension_id'],
        $page['revision_id'],
        $page['revision_id']
        )
      );
      
    unset($_POST);
  }
}

// +-----------------------------------------------------------------------+
// |                            Form display                               |
// +-----------------------------------------------------------------------+

if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_mod.php')
{
  $version_ids_of_revision = get_version_ids_of_revision(
    array($page['revision_id'])
    );

  $language_ids_of_revision = get_language_ids_of_revision(
    array($page['revision_id'])
    );

  $version = isset($_POST['revision_version']) ? $_POST['revision_version'] : $revision_infos_of[ $page['revision_id'] ]['version'];
  $selected_versions = isset($_POST['compatible_versions']) ? $_POST['compatible_versions'] : $version_ids_of_revision[ $page['revision_id'] ];
  $selected_author = isset($_POST['author']) ? $_POST['author'] : $revision_infos_of[ $page['revision_id'] ]['author'];
  $selected_languages = isset($_POST['extensions_languages']) ? $_POST['extensions_languages'] : 
    (!empty($language_ids_of_revision[$page['revision_id']]) ? $language_ids_of_revision[$page['revision_id']] : array());

  $accept_agreement = get_boolean(
    $revision_infos_of[ $page['revision_id'] ]['accept_agreement'],
    false // default value
    );
  
  if ($accept_agreement)
  {
    $accept_agreement_checked = 'checked="checked"';
  }
  else
  {
    $accept_agreement_checked = '';
  }

  // Get descriptions
  $descriptions = array();
  if (isset($_POST['revision_descriptions']))
  {
    $descriptions = $_POST['revision_descriptions'];
    $default_language = $interface_languages[$conf['default_language']]['id'];
  }
  else
  {
    $query = '
SELECT idx_language,
       description
  FROM '.REV_TABLE.'
  WHERE id_revision = '.$page['revision_id'].'
;';
    $result = $db->query($query);
    if ($row = mysql_fetch_assoc($result))
    {
      $descriptions[$row['idx_language']] = $row['description'];
      $default_language = $row['idx_language'];
    }

    $query = '
SELECT idx_language,
       description
  FROM '.REV_TRANS_TABLE.'
  WHERE idx_revision = '.$page['revision_id'].'
;';
    $result = $db->query($query);
    while($row = mysql_fetch_assoc($result))
    {
      $descriptions[$row['idx_language']] = $row['description'];
    }
  }
  
  $tpl->assign('IN_EDIT', true);
}
else
{
  $version = isset($_POST['revision_version']) ? $_POST['revision_version'] : '';
  $descriptions = isset($_POST['revision_descriptions']) ? $_POST['revision_descriptions'] : array();
  $selected_versions = isset($_POST['compatible_versions']) ? $_POST['compatible_versions'] : array();
  $selected_author = isset($_POST['author']) ? $_POST['author'] : $user['id'];
  $selected_languages = isset($_POST['extensions_languages']) ? $_POST['extensions_languages'] : array();
  $default_language = $interface_languages[$conf['default_language']]['id'];

  // Get selected languages of last revision
  $query = '
SELECT MAX(id_revision) as id
  FROM '.REV_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].';';

  if ($last_rev = mysql_fetch_assoc($db->query($query))
    and !empty($last_rev['id']))
  {
    $language_ids_of_revision = get_language_ids_of_revision(array($last_rev['id']));
    $selected_languages = !empty($language_ids_of_revision[$last_rev['id']]) ?
      $language_ids_of_revision[$last_rev['id']] : array();
  }

  // by default the contributor accepts the agreement
  $accept_agreement_checked = 'checked="checked"';
}

if (!in_array($selected_author, $authors))
{
  array_push($authors, $selected_author);
}

$tpl->assign(
  array(
    'extension_name' => $page['extension_name'],
    'use_agreement' => $conf['use_agreement'],
    'agreement_description' => l10n('agreement_description'),
    'authors' => $authors,
    'selected_author' => $selected_author,
    'name' => $version,
    'descriptions' => $descriptions,
    'default_language' => $default_language,
    'accept_agreement_checked' => $accept_agreement_checked,
    'file_needed' => (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_add.php' ? true : false),
    )
  );
  
// Get the main application versions listing
$query = '
SELECT
    id_version,
    version
  FROM '.VER_TABLE.'
;';
$versions = query2array($query);
$versions = versort($versions);
$versions = array_reverse($versions);

// Displays the available versions
$tpl_versions = array();

foreach ($versions as $version)
{
  array_push(
    $tpl_versions,
    array(
      'id_version' => $version['id_version'],
      'name' => $version['version'],
      'selected' =>
        in_array($version['id_version'], $selected_versions)
        ? 'selected="selected"'
        : '',
      )
    );
}

// Get extensions language listing
$query = '
SELECT
    id_language,
    code,
    name
  FROM '.LANG_TABLE.'
  WHERE extensions = "true"
  ORDER BY name
;';
$extensions_languages = query2array($query);
$tpl_languages = array();
foreach($extensions_languages as $ext_lang)
{
  $name = trim(substr($ext_lang['name'], 0, -4));

  array_push(
    $tpl_languages,
    array(
      'id' => $ext_lang['id_language'],
      'code' => $ext_lang['code'],
      'name' => $name,
      'selected' =>
        in_array($ext_lang['id_language'], $selected_languages)
        ? 'selected="selected"'
        : '',
      )
    );
}

$tpl->assign(
  array(
    'versions' => $tpl_versions,
    'extensions_languages' => $tpl_languages,
    'f_action' => $_SERVER['REQUEST_URI'],
    'u_extension' => 'extension_view.php?eid='.$page['extension_id'],
    'page_title' => (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_add.php' ? l10n('Add a revision') : l10n('Modify revision')),
    'translator' => !in_array($user['id'], $authors) and !isAdmin($user['id']),
    'translator_languages' => isTranslator($user['id']) ? $conf['translator_users'][$user['id']] : array(),
  )
);

if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_add.php' and $conf['allow_svn_file_creation']
  and !empty($svn_url) and !empty($archive_name))
{
  $tpl->assign(
    array(
      'allow_svn_file_creation' => true,
      'SVN_URL' => isset($_POST['svn_url']) ? $_POST['svn_url'] : $svn_url,
      'GIT_URL' => isset($_POST['git_url']) ? $_POST['git_url'] : $git_url,
      'SVN_REVISION' => isset($_POST['svn_revision']) ? $_POST['svn_revision'] : 'HEAD',
    )
  );
}

$tpl->assign(
  array(
    'GIT_URL' => isset($_POST['git_url']) ? $_POST['git_url'] : $git_url,
    'GIT_BRANCH' => isset($_POST['git_branch']) ? $_POST['git_branch'] : 'master',
    )
  );

$upload_methods = array('upload', 'git');
if ($conf['allow_download_url'])
{
  array_push($upload_methods, 'url');
  $tpl->assign(
    array(
      'DOWNLOAD_URL' => isset($_POST['download_url']) ? $_POST['download_url'] : '',
      )
    );
}
if ($conf['allow_svn_file_creation'])
{
  array_push($upload_methods, 'svn');
}

$file_type = 'upload';
if (!empty($svn_url))
{
  $file_type = 'svn';
}
elseif (!empty($git_url))
{
  $file_type = 'git';
}

$tpl->assign(
  array(
    'upload_methods' => $upload_methods,
    'FILE_TYPE' => isset($_POST['file_type']) ? $_POST['file_type'] : $file_type,
    )
  );

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+
flush_page_messages();
$tpl->assign_var_from_handle('main_content', 'revision_add');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>
