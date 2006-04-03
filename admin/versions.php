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
  $root_path = './../';
  require_once($root_path . 'include/common.inc.php');
  require_once( $root_path . 'include/functions_admin.inc.php' );
require_once( $root_path . 'admin/init.inc.php' );
  
  $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
  
  switch( $action )
  {
    case 'add' :
      if( isset( $_POST['send'] ) )
      {
        $_POST = escape_array( $_POST );
        
        $sql =  "INSERT INTO " . VER_TABLE;
        $sql .= " (version)";
        $sql .= " VALUES('" . $_POST['version'] . "')";
        
        $db->query( $sql ) or die( 'Erreur lors de l\'ajout de la version' );
        admin_message_success( 'La version a été ajoutée avec succès.', 'versions.php' );
      }
      
      $template->set_file( 'version_add', 'admin/version_add.tpl' );
      
      build_admin_header();
      $template->parse( 'output', 'version_add', true );
      build_admin_footer();
      
    case 'del' :
      if( isset( $_GET['id'] ) )
      {
        $id = intval( $_GET['id'] );
      }
      else
      {
        admin_message_die( 'Identificateur non spécifié.' );
      }
      
      $sql =  "DELETE FROM " . COMP_TABLE;
      $sql .= " WHERE idx_version = '" . $id . "'";
      $db->query( $sql ) or admin_message_die( 'Erreur lors de la suppression de la version.' );
      
      $sql =  "DELETE FROM " . VER_TABLE;
      $sql .= " WHERE id_version = '" . $id . "'";
      $db->query( $sql ) or admin_message_die( 'Erreur lors de la suppression de la version.' );
      
      admin_message_success( 'Version supprimée avec succès.', 'versions.php' );
      break;
      
    case 'mod' :
      if( isset( $_GET['id'] ) )
      {
        $id = intval( $_GET['id'] );
      }
      else
      {
        admin_message_die( 'Identificateur non spécifié.' );
      }
      
      if( isset( $_POST['send'] ) )
      {
        $sql =  "UPDATE " . VER_TABLE;
        $sql .= " SET version = '" . $_POST['version'] . "'";
        $sql .= " WHERE id_version = '" . $id . "'";
        $db->query( $sql ) or die( 'Erreur lors de la modification de la version. Elle existe peut-être déjà.' );
        
        admin_message_success( 'Version ajoutée avec succès.' );
      }
      
      $template->set_file( 'version_mod', 'admin/version_mod.tpl' );
      
      $sql =  "SELECT version";
      $sql .= " FROM " . VER_TABLE;
      $sql .= " WHERE id_version = '" . $id . "'";
      $req = $db->query( $sql );
      $data = $db->fetch_assoc( $req );
      
      $template->set_var( array( 'L_VERSION_NAME' => $data['version'],
                                 'L_VERSION_ID' => $id ) );
      
      build_admin_header();
      $template->parse( 'output', 'version_mod', true );
      build_admin_footer();
      break;
  }
      
  $template->set_file( 'versions', 'admin/versions.tpl' );
  
  $sql =  "SELECT version, id_version";
  $sql .= " FROM " . VER_TABLE;
  $sql .= " ORDER BY version ASC";
  
  $req = $db->query( $sql );
  
  $template->set_block( 'versions', 'version', 't_version' );
  while( $data = $db->fetch_assoc( $req ) )
  {
    $template->set_var( array(  'L_VERSION_ID' => $data['id_version'],
                                'L_VERSION_NAME' => $data['version'] ) );
    $template->parse( 't_version', 'version', true );
  }
  
  build_admin_header();
  $template->parse( 'output', 'versions', true );
  build_admin_footer();
?>
