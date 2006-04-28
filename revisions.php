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

// This file has no possible action, the only action used is the "add" one.
// The revisions modification is defined in the "contributions.php" file.
  
define( 'INTERNAL', true );
$root_path = './';
require_once( $root_path . 'include/common.inc.php' );
  
if (!isset($user['id']))
{
  message_die( 'Vous devez être connecté pour pouvoir accéder à cette section.' );
}

$page['extension_id'] =
  (isset($_GET['extension_id']) and is_numeric($_GET['extension_id']))
  ? $_GET['extension_id']
  : '';

if (empty($page['extension_id']))
{
  message_die(l10n('Incorrect extension identifier'));
}

echo 'extension id: ', $page['extension_id'], '<br>';
    
  // The add form has ben submitted
  if( isset( $_POST['send'] ) )
  {
    // Check file extension
    if( strtolower( substr( $_FILES['revision_file']['name'], -3) ) != 'zip' )
    {
      message_die( 'L\'extension du fichier n\'est pas correcte. Le fichier doit être un .zip !' );
    }

    // Check file existence. File overwriting isn't allowed
    if( file_exists( EXTENSIONS_DIR . $_FILES['revision_file']['name'] ) )
      message_die( 'L\'extension proposée existe déjà.' );
      
    // Check file size
    if( $_FILES['revision_file']['error'] == UPLOAD_ERR_INI_SIZE )
    {
      message_die( 'Le fichier est trop gros. Sa taille ne doit pas excéder ' . 
      ini_get( 'upload_max_filesize' ) . '.' );
    }

    // Checks that all the fields have been well filled
    if(empty( $_POST['revision_changelog'] ) or empty( $_POST['revision_version'] )
       or empty( $_POST['revision_compatibility'] ) or empty( $_POST['revision_extension'] ))
    {
      message_die( 'Vous n\'avez pas rempli tous les champs.' );
    }
       
    // Escapes the array by using the mysql_escape_string( ) function
    $_POST = escape_array( $_POST );
      
    // Moves the file to its final destination
    move_uploaded_file(
      $_FILES['revision_file']['tmp_name'],
      EXTENSIONS_DIR.$_FILES['revision_file']['name']
      );
    
    // Inserts the revision
    $sql =  "INSERT INTO " . REV_TABLE . " (idx_extension, date, url, description, version)";
    $sql .= " VALUES ('" . $_POST['revision_extension'] . "', '" . mktime() . "', '";
    $sql .= $_FILES['revision_file']['name'] . "', '" . $_POST['revision_changelog'];
    $sql .= "', '" . $_POST['revision_version'] . "')";
    $db->query( $sql ) or die('Erreur durant l\'insertion de la révision. Contactez l\'administrateur' );
    $revision_id = $db->insert_id();
    
    // Inserts the revisions <-> compatibilities link
    foreach($_POST['revision_compatibility'] as $compatibility)
    {
      $sql =  "INSERT INTO " . COMP_TABLE . " (idx_revision, idx_version)";
      $sql .= " VALUES ('" . $revision_id . "', '" . $compatibility . "')";
      $db->query( $sql ) or die( 'Erreur durant l\'insertion de la révision. Contactez l\'administrateur' );
    }
    
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
  
  // Author's Extensions list
  $sql =  "SELECT id_extension, name";
  $sql .= " FROM " . EXT_TABLE;
  $sql .= " WHERE idx_author = '" . $user['id'] . "'";
  $sql .= " ORDER BY name ASC";
  $req = $db->query( $sql );
  
  $template->set_block( 'revision_add', 'revision_extension', 'Trevision_extension' );
  while( $data = $db->fetch_assoc( $req ) )
  {
    $template->set_var(
      array(
        'L_REVISION_EXTENSION_VALUE' => $data['id_extension'],
        'L_REVISION_EXTENSION_NAME' => $data['name'],
        )
      );
    $template->parse( 'Trevision_extension', 'revision_extension', true );
  }
  
  // Get the PWG versions listing
  $sql =  "SELECT version, id_version";
  $sql .= " FROM " . VER_TABLE;
  $sql .= " ORDER BY version ASC";
  $req = $db->query($sql);
  
  // Displays the available versions
  $template->set_block('revision_add', 'revision_compatibility', 'Trevision_compatibility');
  while($data = $db->fetch_assoc($req))
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