<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2009 PEM Team - http://home.gna.org/pem            |
// +-----------------------------------------------------------------------+
// | last modifier : $Author: plg $
// | revision      : $Revision: 2 $
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
list($page['extension_name'], $ext_user, $svn_url, $archive_root_dir, $archive_name) = $db->fetch_array($result);

$authors = get_extension_authors($page['extension_id']);

if (!in_array($user['id'], $authors) and !isAdmin($user['id']))
{
  message_die('You must be the extension author to modify it.');
}

// +-----------------------------------------------------------------------+
// |                           Form submission                             |
// +-----------------------------------------------------------------------+

if (isset($_POST['submit']))
{
  // The file is mandatory only when we add a revision, not when we modify it
  if (basename($_SERVER['SCRIPT_FILENAME']) != 'revision_add.php')
  {
    $file_to_upload = 'none';
  }
  elseif (isset($_POST['file_type']) and $_POST['file_type'] == 'svn')
  {
    $file_to_upload = 'svn';
  }
  else
  {
    $file_to_upload = 'user';
  }

  if ($file_to_upload == 'user')
  {
    // Check file extension
    if (strtolower(substr($_FILES['revision_file']['name'], -3)) != 'zip')
    {
      message_die('Only *.zip files are allowed');
    }
  
    // Check file size
    if ($_FILES['revision_file']['error'] == UPLOAD_ERR_INI_SIZE)
    {
      message_die(
        sprintf(
          l10n('File too big. Filesize must not exceed %s.'),
          ini_get('upload_max_filesize')
        )
      );
    }
  }

  if ($file_to_upload == 'svn')
  {
    $svn_url = $_POST['svn_url'];
    if (empty($svn_url))
    {
      message_die('Some fields are missing');
    }

    $temp_svn_path = $conf['local_data_dir'] . '/svn_import';
    if (!is_dir($temp_svn_path))
    {
      umask(0000);
      if (!mkdir($temp_svn_path, 0777))
      {
        die("problem during ".$temp_svn_path." creation");
      }
    }

    // Create random path
    $temp_svn_path .= '/' . md5(uniqid(rand(), true));

    // SVN export
    $svn_command = $conf['svn_path'] . ' export';
    $svn_command .= is_numeric($_POST['svn_revision']) ? ' -r'.$_POST['svn_revision'] : '';
    $svn_command .= ' ' . escapeshellarg($svn_url);
    $svn_command .= ' ' . $temp_svn_path;

    exec($svn_command, $svn_infos);

    if (empty($svn_infos))
    {
      message_die('An error occured during SVN export.');
    }

    $archive_name = str_replace('%', @$_POST['revision_version'], $archive_name);
    $svn_revision = preg_replace('/exported revision (\d+)\./i', '$1', end($svn_infos));

    if (!empty($conf['archive_comment']) and !file_exists($temp_svn_path.'/'.$conf['archive_comment_filename']))
    {
      file_put_contents(
        $temp_svn_path.'/'.$conf['archive_comment_filename'],
        sprintf($conf['archive_comment'], $svn_url, $svn_revision)
      );
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
      @deltree($temp_svn_path);
      message_die('Some fields are missing');
    }
  }
  if (empty($_POST['revision_descriptions'][@$_POST['default_description']]))
  {
    @deltree($temp_svn_path);
    message_die('Default description can not be empty');
  }
  
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
          'version'        => $_POST['revision_version'],
          'description'    => $_POST['revision_descriptions'][$_POST['default_description']],
          'idx_language'   => $_POST['default_description'],
          'author'         => isset($_POST['author']) ? $_POST['author'] : $revision_infos_of[$page['revision_id']]['author'],
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
      'version'        => $_POST['revision_version'],
      'idx_extension'  => $page['extension_id'],
      'date'           => mktime(),
      'description'    => $_POST['revision_descriptions'][$_POST['default_description']],
      'idx_language'   => $_POST['default_description'],
      'url'            => ($file_to_upload == 'user' ? $_FILES['revision_file']['name'] : $archive_name),
      'author'         => isset($_POST['author']) ? $_POST['author'] : $user['id'],
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
      if (!mkdir($extension_dir, 0777))
      {
        die("problem during ".$extension_dir." creation");
      }
    }
    
    umask(0000);
    @mkdir($revision_dir, 0777);

    if ($file_to_upload == 'user')
    {
      move_uploaded_file(
        $_FILES['revision_file']['tmp_name'],
        $revision_dir.'/'.$_FILES['revision_file']['name']
      );
    }
    else
    {
      // Create zip archive
      include_once($root_path.'include/pclzip.lib.php');
      $zip = new PclZip($revision_dir.'/'.$archive_name);
      $zip->create($temp_svn_path,
        PCLZIP_OPT_REMOVE_PATH, $temp_svn_path,
        PCLZIP_OPT_ADD_PATH, $archive_root_dir);
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
        'description'    => $desc,
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
        'idx_version'   => $version_id,
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
          'idx_language'  => $language_id,
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

  $version = $revision_infos_of[ $page['revision_id'] ]['version'];
  $selected_versions = $version_ids_of_revision[ $page['revision_id'] ];
  $selected_author = $revision_infos_of[ $page['revision_id'] ]['author'];
  $selected_languages = !empty($language_ids_of_revision[$page['revision_id']]) ?
    $language_ids_of_revision[$page['revision_id']] : array();

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
else
{
  $version = '';
  $descriptions = array();
  $selected_versions = array();
  $selected_author = $user['id'];
  $selected_languages = array();
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
$versions = array_of_arrays_from_query($query);
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
      'checked' =>
        in_array($version['id_version'], $selected_versions)
        ? 'checked="checked"'
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
$extensions_languages = array_of_arrays_from_query($query);
$tpl_languages = array();
foreach($extensions_languages as $ext_lang)
{
  array_push(
    $tpl_languages,
    array(
      'id' => $ext_lang['id_language'],
      'code' => $ext_lang['code'],
      'name' => $ext_lang['name'],
      'checked' => in_array($ext_lang['id_language'], $selected_languages) ? 'checked="checked"' : '',
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
  )
);

if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_add.php' and $conf['allow_svn_file_creation']
  and !empty($svn_url) and !empty($archive_root_dir) and !empty($archive_name))
{
  $tpl->assign(
    array(
      'allow_svn_file_creation' => true,
      'SVN_URL' => $svn_url,
    )
  );
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'revision_add');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>
