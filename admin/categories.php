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
        $_POST = escape_array( $_POST );
        
        if( $_POST['parent'] == 0 )
          $parent = 'NULL';
        else
          $parent = "'" . $_POST['parent'] . "'";
          
        $sql =  "UPDATE " . CAT_TABLE;
        $sql .= " SET name = '" . $_POST['name'] . "', idx_parent = " . $parent . ",";
        $sql .= " description = '" . $_POST['description'] . "'";
        $sql .= " WHERE id_category = '" . $id . "'";
        $db->query( $sql ) or admin_message_die( 'Erreur lors de la modification de la catégorie.' );
        
        admin_message_success( 'Catégorie modifiée avec succès.', 'categories.php' );
      }
      
      $template->set_file( 'category_mod', 'admin/category_mod.tpl' );
      
      $sql =  "SELECT name, idx_parent, description";
      $sql .= " FROM " . CAT_TABLE;
      $sql .= " WHERE id_category = '" . $id . "'";
      $req = $db->query( $sql );
      $data_category = $db->fetch_assoc( $req );
      
      $template->set_var( array( 'L_CATEGORY_NAME' => $data_category['name'],
                                 'L_CATEGORY_DESCRIPTION' => $data_category['description'],
                                 'L_CATEGORY_ID' => $id ) );
                                 
      $sql =  "SELECT id_category, name";
      $sql .= " FROM " . CAT_TABLE;
      $sql .= " WHERE id_category != '" . $id . "' AND idx_parent IS NULL";
      $sql .= " ORDER BY name ASC";
      $req = $db->query( $sql );
      
      $template->set_block( 'category_mod', 'category', 't_category' );
      while( $data = $db->fetch_assoc( $req ) )
      {
        $template->set_var( array( 'L_LIST_CATEGORY_ID' => $data['id_category'],
                                   'L_LIST_CATEGORY_NAME' => $data['name'] ) );
        if( $data['id_category'] == $data_category['idx_parent'] )
          $template->set_var( 'L_LIST_CATEGORY_SELECTED', 'selected' );
        $template->parse( 't_category', 'category', true );
        $template->clear_var( 'L_LIST_CATEGORY_SELECTED' );
      }
      
      build_admin_header();
      $template->parse( 'output', 'category_mod', true );
      build_admin_footer();
      
      break;
        
    case 'del' :
      if( isset( $_GET['id'] ) )
      {
        $id = intval( $_GET['id'] );
      }
      else
      {
        admin_message_die( 'Identificateur non spécifié.' );
      }
      
      $sql =  "DELETE FROM " . CAT_TABLE;
      $sql .= " WHERE idx_parent = '" . $id . "'";
      $db->query( $sql ) or admin_message_die( 'Erreur lors de la suppression de la catégorie.' );
      $sql =  "DELETE FROM " . CAT_TABLE;
      $sql .= " WHERE id_category = '" . $id . "'";
      $db->query( $sql ) or admin_message_die( 'Erreur lors de la suppression de la catégorie.' );
      
      admin_message_success( 'Catégorie supprimée avec succès.', 'categories.php' );
      break;
      
    case 'add':
      // Formulaire validé
      if( isset( $_POST['send'] ) )
      {
        escape_array( $_POST );
        
        if( $_POST['parent'] == 0 )
          $parent = 'NULL';
        else
          $parent = "'" . $_POST['parent'] . "'";
          
        $sql =  "INSERT INTO " . CAT_TABLE;
        $sql .= " (name, idx_parent, description)";
        $sql .= " VALUES('" . $_POST['name'] . "', " . $parent . ",";
        $sql .= " '" . $_POST['description'] . "')";
        
        $db->query( $sql ) or admin_message_die( 'Erreur lors de l\'ajout de la catégorie.' );
        
        admin_message_success( 'Catégorie ajoutée avec succès.', 'categories.php' );
      }
      
      $template->set_file( 'category_add', 'admin/category_add.tpl' );
      
      $sql =  "SELECT name, id_category";
      $sql .= " FROM " . CAT_TABLE;
      $sql .= " WHERE idx_parent IS NULL";
      $sql .= " ORDER BY name ASC";
      
      $req = $db->query( $sql );
      
      $template->set_block( 'category_add', 'category', 't_category' );
      while( $data = $db->fetch_assoc( $req ) )
      {
        $template->set_var( array( 'L_CATEGORY_ID' => $data['id_category'],
                                   'L_CATEGORY_NAME' => $data['name'] ) );
        $template->parse( 't_category', 'category', true );
      }
        
      build_admin_header();
      $template->parse( 'output', 'category_add', true );
      build_admin_footer();
    break;
  }
  
  // Categories selection
  $sql =  "SELECT id_category, idx_parent, name, description";
  $sql .= " FROM " . CAT_TABLE;
  $sql .= " ORDER BY idx_parent ASC, name ASC";
  $req = $db->query($sql);
  
  $categories = array();
  while($data = $db->fetch_assoc($req))
    $categories[] = $data;
  
  $template->set_file( 'categories', 'admin/categories.tpl' );
  $template->set_block( 'categories', 'category_sublevel_item', 'Tcategory_sublevel_item');
  $template->set_block( 'categories', 'category_sublevel', 'Tcategory_sublevel');
  $template->set_block( 'categories', 'category', 'Tcategory');
 
  // Browse the categories and display them
  foreach($categories as $cat)
  {
    // This is not a main category
    if(!empty($cat['idx_parent']))
      continue;
      
    $has_sublevels = false;
    
    // Display sub-categories
    foreach($categories as $cat_sublevel)
    {
      if($cat_sublevel['idx_parent'] == $cat['id_category'])
      {
        $template->set_var(array( 'L_CATEGORY_SUBLEVEL_ITEM_ID' => $cat_sublevel['id_category'],
                                  'L_CATEGORY_SUBLEVEL_ITEM' => $cat_sublevel['name'],
                                  'L_CATEGORY_SUBLEVEL_DESCRIPTION' => $cat_sublevel['description']));
        $template->parse('Tcategory_sublevel_item', 'category_sublevel_item', true);
        $has_sublevels = true;
      }
    }
    
    $template->set_var(array( 'L_CATEGORY_NAME' => $cat['name'],
                              'L_CATEGORY_DESCRIPTION' => $cat['description'],
                              'L_CATEGORY_ID' => $cat['id_category'] ));
                                
    
    if($has_sublevels)
      $template->parse('Tcategory_sublevel', 'category_sublevel', true);
      
    $template->parse('Tcategory', 'category', true);
    $template->clear_var('Tcategory_sublevel');
    $template->clear_var('Tcategory_sublevel_item');
  }
  
  build_admin_header();
  $template->parse( 'output', 'categories', true );
  build_admin_footer();

?>
