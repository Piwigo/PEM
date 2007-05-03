<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2006 PEM Team - http://home.gna.org/pem            |
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

define('INTERNAL', true);
$root_path = './';
require_once($root_path.'include/common.inc.php');
  
if (!isset($user['id']))
{
  message_die(l10n('You must be connected to reach this page'));
}

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
  message_die(l10n('Incorrect extension identifier'));
}

$query = '
SELECT name
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
$result = $db->query($query);

if ($db->num_rows($result) == 0)
{
  message_die(l10n('Unknown extension'));
}
list($page['extension_name']) = $db->fetch_array($result);

// +-----------------------------------------------------------------------+
// |                           Form submission                             |
// +-----------------------------------------------------------------------+

if (isset($_POST['submit']))
{
  // The file is mandatory only when we add a revision, not when we modify
  // it
  if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_add.php'
      or !empty($_FILES['revision_file']['name']))
  {
    $file_to_upload = true;
  }
  else
  {
    $file_to_upload = false;
  }

  if ($file_to_upload)
  {
    // Check file extension
    if (strtolower(substr($_FILES['revision_file']['name'], -3)) != 'zip')
    {
      message_die(l10n('Only *.zip files are allowed'));
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

  $required_fields = array(
    'revision_changelog',
    'revision_version',
    'compatible_versions',
    );
  
  foreach ($required_fields as $field)
  {
    if (empty($_POST[$field]))
    {
      message_die(l10n('Some fields are missing.'));
    }
  }
  
  // Escapes the array by using the mysql_escape_string( ) function
  $_POST = escape_array($_POST);

  if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_mod.php')
  {
    mass_updates(
      REV_TABLE,
      array(
        'primary' => array('id_revision'),
        'update'  => array('version', 'description'),
        ),
      array(
        array(
          'id_revision'    => $page['revision_id'],
          'version'        => $_POST['revision_version'],
          'description'    => $_POST['revision_changelog'],
          ),
        )
      );
  }
  else
  {
    $insert = array(
      'version'        => $_POST['revision_version'],
      'idx_extension'  => $page['extension_id'],
      'date'           => mktime(),
      'description'    => $_POST['revision_changelog'],
      'url'            => $_FILES['revision_file']['name'],
      );
    
    mass_inserts(
      REV_TABLE,
      array_keys($insert),
      array($insert)
      );

    $page['revision_id'] = $db->insert_id();

  }

  if ($file_to_upload)
  {
    // Moves the file to its final destination:
    // upload/extension-X/revision-Y
    $extension_dir = EXTENSIONS_DIR.'extension-'.$page['extension_id'];
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
    mkdir($revision_dir, 0777);
    
    move_uploaded_file(
      $_FILES['revision_file']['tmp_name'],
      $revision_dir.'/'.$_FILES['revision_file']['name']
      );
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
  
  // Updates the RSS
  create_rss();
    
  message_success(
    l10n('revision successfuly added. Thank you.'),
    'revision_view.php?rid='.$page['revision_id']
    );
}

// +-----------------------------------------------------------------------+
// |                            Form display                               |
// +-----------------------------------------------------------------------+

$tpl->assign(
  array(
    'extension_name' => $page['extension_name'],
    )
  );

if (isset($_POST['submit']))
{
  $version = @$_POST['revision_version'];
  $description = @$_POST['revision_description'];
  $selected_versions = $_POST['compatible_versions'];
}
else if (basename($_SERVER['SCRIPT_FILENAME']) == 'revision_mod.php')
{
  $version_ids_of_revision = get_version_ids_of_revision(
    array($page['revision_id'])
    );

  $version = $revision_infos_of[ $page['revision_id'] ]['version'];
  $description = $revision_infos_of[ $page['revision_id'] ]['description'];
  $selected_versions = $version_ids_of_revision[ $page['revision_id'] ];
}
else
{
  $version = '';
  $description = '';
  $selected_versions = array();
}

// echo '<pre>'; print_r($version); echo '</pre>';
// echo '<pre>'; echo "#".$version."#"; echo '</pre>';

$tpl->assign(
  array(
    'name' => $version,
    'description' => $description,
    )
  );
  
// Get the main application versions listing
$query = '
SELECT version,
       id_version
  FROM '.VER_TABLE.'
  ORDER BY version ASC
';
$req = $db->query($query);
  
// Displays the available versions
$tpl_versions = array();

while ($data = $db->fetch_assoc($req))
{
  array_push(
    $tpl_versions,
    array(
      'id_version' => $data['id_version'],
      'name' => $data['version'],
      'checked' =>
        in_array($data['id_version'], $selected_versions)
        ? 'checked="checked"'
        : '',
      )
    );
}

$tpl->assign('versions', $tpl_versions);

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign('main_content', 'revision_add.jtpl');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->display('page.jtpl');
?>