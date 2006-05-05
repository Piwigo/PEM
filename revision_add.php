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

$page['extension_id'] =
  (isset($_GET['extension_id']) and is_numeric($_GET['extension_id']))
  ? $_GET['extension_id']
  : '';

if (empty($page['extension_id']))
{
  message_die(l10n('Incorrect extension identifier'));
}

// The add form has ben submitted
if (isset($_POST['submit']))
{
  // Check file extension
  if (strtolower(substr($_FILES['revision_file']['name'], -3)) != 'zip')
  {
    message_die(l10n('Only *.zip files are allowed'));
  }
  
  // Check file size
  if( $_FILES['revision_file']['error'] == UPLOAD_ERR_INI_SIZE )
  {
    message_die(
      sprintf(
        l10n('File too big. Filesize must not exceed %s.'),
        ini_get('upload_max_filesize')
        )
      );
  }

  $required_fields = array(
    'revision_changelog',
    'revision_version',
    'revision_compatibility',
    );
  
  foreach ($required_fields as $field)
  {
    if (empty($_POST[$field]))
    {
      message_die(l10n('Some fields are missing.'));
    }
  }
  
  // Escapes the array by using the mysql_escape_string( ) function
  $_POST = escape_array( $_POST );

  $insert = array(
    'version'        => $_POST['revision_version'],
    'idx_extension'  => $page['extension_id'],
    'date'           => mktime(),
    'description'    => $_POST['revision_changelog'],
    'url'            => $_FILES['revision_file']['name'],
    );
  mass_inserts(REV_TABLE, array_keys($insert), array($insert));

  $revision_id = $db->insert_id();
  
  // Moves the file to its final destination: upload/extension-X/revision-Y
  $extension_dir = EXTENSIONS_DIR.'extension-'.$page['extension_id'];
  $revision_dir = $extension_dir.'/revision-'.$revision_id;
  
  if (!is_dir($extension_dir))
  {
    umask(0000);
    mkdir($extension_dir, 0777);
  }

  umask(0000);
  mkdir($revision_dir, 0777);
  
  move_uploaded_file(
    $_FILES['revision_file']['tmp_name'],
    $revision_dir.'/'.$_FILES['revision_file']['name']
    );
  
  // Inserts the revisions <-> compatibilities link
  $inserts = array();
  foreach ($_POST['revision_compatibility'] as $compatibility)
  {
    array_push(
      $inserts,
      array(
        'idx_revision'  => $revision_id,
        'idx_version'   => $compatibility,
        )
      );
  }
  mass_inserts(COMP_TABLE, array_keys($inserts[0]), $inserts);
  
  // Updates the RSS
  create_rss();
    
  message_success( 'La révision a été ajoutée avec succès. Merci de votre participation.', 'index.php' );
}
  
$template->set_file( 'revision_add', 'revision_add.tpl' );

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

$data = $db->fetch_array($result);

$template->set_var(
  array(
    'EXTENSION_NAME' => $data['name'],
    )
  );
  
// Get the PWG versions listing
$query = '
SELECT version,
       id_version
  FROM '.VER_TABLE.'
  ORDER BY version ASC
';
$req = $db->query($query);
  
// Displays the available versions
$template->set_block('revision_add', 'revision_compatibility', 'Trevision_compatibility');
while ($data = $db->fetch_assoc($req))
{
  $template->set_var(
    array(
      'L_REVISION_COMP_VALUE' => $data['id_version'],
      'L_REVISION_COMP_NAME' => $data['version'],
      )
    );
  $template->parse( 'Trevision_compatibility', 'revision_compatibility', true );
}

build_header();
$template->parse( 'output', 'revision_add', true );
build_footer();

?>