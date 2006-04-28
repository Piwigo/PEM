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
  require_once($root_path . 'include/common.inc.php');

  $action = isset( $_GET['action'] ) ? $_GET['action'] : '';
  $page = isset( $_GET['page'] ) ? abs(intval($_GET['page'])) : 1;
  $page = ( $page <= 0 ) ? 1 : $page;
  $id = isset( $_GET['id'] ) ? abs(intval($_GET['id'])) : '';
  
// Access allowed only for registered users
if (!isset($user['id']))
{
  message_die( 'Vous devez être connecté pour pouvoir accéder à cette section.' );
}
    
  switch( $action )
  {
    // Extensions deleting
    case 'del_ext' :
    {
      if( empty( $id ) )
        message_die( 'Identificateur non spécifié.' );
        
      // Checks if the user who wants to delete the extension is really its author
      $sql =  "SELECT idx_author";
      $sql .= " FROM " . EXT_TABLE;
      $sql .= " WHERE id_extension = '" . $id . "'";
      $req = $db->query( $sql );
      $data = $db->fetch_assoc( $req );
      
      if( empty( $data['idx_author'] ) )
      {
        message_die( 'L\'extension n\'a pas été trouvée. ');
      }
        
      if( $data['idx_author'] != $user['id'] && !isAdmin($user['id']) )
      {
        message_die( 'Vous ne pouvez supprimer que les extensions qui vous appartiennent !' );
      }

      // Gets all the revisions for the given extension and delete their compatibility relations
      $sql =  "SELECT id_revision, url";
      $sql .= " FROM " . REV_TABLE;
      $sql .= " WHERE idx_extension = '" . $id . "'";
      $req = $db->query( $sql ) or message_die( 'Erreur lors de la suppression de l\'extension.');
      
      while($data = $db->fetch_assoc( $req ) )
      {
        unlink( EXTENSIONS_DIR . $data['url'] );
        
        $sql =  "DELETE FROM " . COMP_TABLE;
        $sql .= " WHERE idx_revision = '" . $data['id_revision'] . "'";
        $db->query( $sql ) or message_die( 'Erreur lors de la suppression de l\'extension.');
      }
      
      // Deletes all the revisions
      $sql =  "DELETE FROM " . REV_TABLE;
      $sql .= " WHERE idx_extension = '" . $id . "'";
      $db->query( $sql ) or message_die( 'Erreur lors de la suppression de l\'extension.');
      
      // Deletes all the categories relations
      $sql =  "DELETE FROM " . EXT_CAT_TABLE;
      $sql .= " WHERE idx_extension = '" . $id . "'";
      $db->query( $sql ) or message_die( 'Erreur lors de la suppression de l\'extension.');
      
      // And finally delete the extension
      $sql =  "DELETE FROM " . EXT_TABLE;
      $sql .= " WHERE id_extension = '" . $id . "'";
      $db->query( $sql ) or message_die( 'Erreur lors de la suppression de l\'extension.' );

      message_success( 'L\'extension a été supprimée avec succès.', 'contributions.php' );

      break;
    }
    // Revision deleting
    case 'del_rev' :
    {
      if( empty( $id ) )
        message_die( 'Identificateur non spécifié.' );
        
      // Checks if the user who wants to delete the revision is really its author
      $sql =  "SELECT idx_author, url, idx_extension";
      $sql .= " FROM " . REV_TABLE;
      $sql .= " INNER JOIN " . EXT_TABLE . " ON idx_extension = id_extension";
      $sql .= " WHERE id_revision = '" . $id . "'";

      $req = $db->query( $sql );
      $data = $db->fetch_assoc( $req );
      
      if( empty( $data['idx_author'] ) )
      {
        message_die( 'La révision n\'a pas été trouvée. ');
      }
        
      if( $data['idx_author'] != $user['id'] && !isAdmin($user['id']) )
      {
        message_die( 'Vous ne pouvez supprimer que les révisions qui vous appartiennent !' );
      }
      
      // Checks if the revision is the last one of the extension, if yes, display an error
      $sql =  "SELECT COUNT( id_revision ) AS revisions_count";
      $sql .= " FROM " . REV_TABLE;
      $sql .= " WHERE idx_extension = '" . $data['idx_extension'] . "'";
      $req_rev = $db->query( $sql );
      $data_rev = $db->fetch_assoc( $req_rev );
      
      if( $data_rev['revisions_count'] == 1 )
        message_die( 'Vous ne pouvez pas supprimer la dernière révision de l\'extension. Supprimez l\'extension à la place.' );
      
      unlink( EXTENSIONS_DIR . $data['url'] );
      
      // Deletes the compatibility relations
      $sql =  "DELETE FROM " . COMP_TABLE;
      $sql .= " WHERE idx_revision = '" . $id . "'";
      $db->query( $sql ) or message_die( 'Erreur lors de la suppression de la révision.' );
      
      // ... and finally delete the revision
      $sql =  "DELETE FROM " . REV_TABLE;
      $sql .= " WHERE id_revision = '" . $id . "'";
      $db->query($sql) or message_die( 'Erreur lors de la suppression de la révision.' );

      message_success( 'La révision a été supprimée avec succès.', 'contributions.php' );

      break;
    }
    // Revision modification
    case 'mod_rev' :
    {
      if( isset( $_POST['send'] ) )
      {
        $id = isset( $_POST['id'] ) ? intval( $_POST['id'] ) : '';
        
        if( empty( $id ) )
          message_die( 'Identificateur non spécifié.' );
        
        $sql =  "SELECT 1 as is_author";
        $sql .= " FROM " . EXT_TABLE;
        $sql .= " INNER JOIN " . REV_TABLE . " ON id_revision = '" . $id . "'";
        $sql .= " WHERE idx_author = '" . $user['id'] . "'";
        
        $req = $db->query( $sql );
        
        if( $db->num_rows( $req ) == 0 && !isAdmin($user['id']) )
          message_die( 'Vous n\'êtes pas l\'auteur de cette révision.' );
        
        // Checks that all the fields have been well filled
        if(empty( $_POST['revision_changelog'] ) or empty( $_POST['revision_version'] )
           or empty( $_POST['revision_compatibility'] ) or empty( $_POST['revision_extension'] ))
        {
          message_die( 'Vous n\'avez pas rempli tous les champs.' );
        }
        
        $sql_new_file = '';
        if( !empty( $_FILES['revision_file']['name'] ) )
        {
          // Checks file extension
          if( strtolower( substr( $_FILES['revision_file']['name'], -3 ) ) != 'zip' )
          {
            message_die( 'L\'extension du fichier n\'est pas correcte. Le fichier doit être un .zip !' );
          }
  
          // Checks file existence
          if( file_exists( EXTENSIONS_DIR . $_FILES['revision_file']['name'] ) )
          {
            message_die( 'L\'extension proposée existe déjà.' );
          }
            
          // Checks file size
          if( $_FILES['revision_file']['error'] == UPLOAD_ERR_INI_SIZE )
          {
            message_die( 'Le fichier est trop gros. Sa taille ne doit pas excéder ' . 
            ini_get( 'upload_max_filesize' ) . '.' );
          }
          
          // Moves the file to its final destination
          move_uploaded_file( $_FILES['revision_file']['tmp_name'], EXTENSIONS_DIR . 
                              $_FILES['revision_file']['name'] );
                              
          $sql_new_file = ", url = '" . $_FILES['revision_file']['name'] . "'";
        }
           
        // Escapes the array by using the mysql_escape_array( ) function
        $_POST = escape_array( $_POST );

        // Updates the revision informations
        $sql =  "UPDATE " . REV_TABLE;
        $sql .= " SET version = '" . $_POST['revision_version'] . "', description = '" . $_POST['revision_changelog'] . "',";
        $sql .= " idx_extension = '" . $_POST['revision_extension'] . "'" . $sql_new_file;
        $sql .= " WHERE id_revision = '" . $id . "'";
        $db->query( $sql );
        
        // Updates the compatibility informations
        $sql =  "DELETE FROM " . COMP_TABLE;
        $sql .= " WHERE idx_revision = '" . $id . "'";
        $db->query( $sql );
        
        foreach( $_POST['revision_compatibility'] as $compatibility )
        {
          $sql =  "INSERT INTO " . COMP_TABLE . " (idx_revision, idx_version)";
          $sql .= " VALUES('" . $id . "', '" . intval( $compatibility ) . "')";
          $db->query( $sql );
        }
        
        message_success( 'La révision a été modifiée avec succès.', 'contributions.php' ); 
        
      }
      
      $template->set_file( 'revision_mod', 'revision_mod.tpl' );
      
      if( empty( $id ) )
        message_die( 'Identificateur non spécifié.' );
        
      // Gets the extensions listing
      $sql =  "SELECT name, id_extension, id_revision";
      $sql .= " FROM " . EXT_TABLE;
      $sql .= " LEFT JOIN " . REV_TABLE . " ON idx_extension = id_extension AND id_revision = '" . $id . "'";
      $sql .= " WHERE idx_author = '" . $user['id'] . "'";
      $sql .= " ORDER BY name ASC";

      $req = $db->query( $sql );
      
      // Checks if the user who wants to modify the revision is really its author
      if( $db->num_rows( $req ) == 0 && !isAdmin($user['id']) )
        message_die( 'Vous ne pouvez modifier que vos révisions.' );
      
      $extensions_array = array();
      $i = 0;
      while( $data = $db->fetch_assoc( $req ) )
      {
        $extensions_array[$i]['id'] = $data['id_extension'];
        
        if( empty( $data['id_revision'] ) )
          $extensions_array[$i]['is_selected'] = '';
        else 
          $extensions_array[$i]['is_selected'] = 'selected';
          
        $extensions_array[$i++]['name'] = $data['name'];
      }

      // Gets the PWG versions listing
      $sql =  "SELECT id_version, version, idx_revision";
      $sql .= " FROM " . VER_TABLE;
      $sql .= " LEFT JOIN " . COMP_TABLE . " ON idx_version = id_version AND idx_revision = '" . $id . "'";
      $sql .= " ORDER BY version ASC";

      $req = $db->query( $sql );
      
      $versions_array = array();
      $i = 0;
      
      while( $data = $db->fetch_assoc( $req ) )
      {
        $versions_array[$i]['id'] = $data['id_version'];
        
        if( empty( $data['idx_revision'] ) )
          $versions_array[$i]['is_selected'] = '';
        else 
          $versions_array[$i]['is_selected'] = 'selected';
          
        $versions_array[$i++]['version'] = $data['version'];
      }
      
      // Gets the revision informations
      $sql =  "SELECT description, version, id_revision";
      $sql .= " FROM " . REV_TABLE;
      $sql .= " WHERE id_revision = '" . $id . "'";
      
      $req = $db->query( $sql );
      $data_revision = $db->fetch_assoc( $req );
      
      $template->set_block( 'revision_mod', 'revision_extension', 't_revision_extension' );
      foreach( $extensions_array as $extension )
      {
        $template->set_var( array( 'L_REVISION_EXTENSION_VALUE' => $extension['id'],
                                   'L_REVISION_EXTENSION_NAME' => $extension['name'],
                                   'L_REVISION_EXTENSION_SELECTED' => $extension['is_selected'] ) );
        $template->parse( 't_revision_extension', 'revision_extension', true );
      }
      
      $template->set_block( 'revision_mod', 'revision_compatibility', 't_revision_compatibility' );
      foreach( $versions_array as $version )
      {
        $template->set_var( array( 'L_REVISION_COMP_VALUE' => $version['id'],
                                   'L_REVISION_COMP_NAME' => $version['version'],
                                   'L_REVISION_COMP_SELECTED' => $version['is_selected'] ) );
        $template->parse( 't_revision_compatibility', 'revision_compatibility', true );
      }
      
      $template->set_var( array( 'L_REVISION_VERSION' => $data_revision['version'],
                                 'L_REVISION_ID' => $data_revision['id_revision'],
                                 'L_REVISION_CHANGELOG' => htmlspecialchars( $data_revision['description'] ) ) );

      build_header();
      $template->parse( 'output', 'revision_mod', true );
      build_footer();

      break;
    }
    // Extension modification
    case 'mod_ext' :
    {
      if( isset( $_POST['extension_id'] ) )
      {
        $id = intval( $_POST['extension_id'] );
      }
      else if( isset( $_GET['id'] ) )
      {
        $id = intval( $_GET['id'] );
      }
      else
      {
        message_die( 'Identificateur non spécifié.' );
      }

      // Gets the author information (and check if the user who wants to modify the ext
      // is really its author
      $sql =  "SELECT idx_author";
      $sql .= " FROM " . EXT_TABLE;
      $sql .= " WHERE id_extension = '" . $id . "'";
      $req = $db->query( $sql );
      $data = $db->fetch_assoc( $req );
      
      if( empty( $data['idx_author'] ) )
        message_die( 'Extension non trouvée.' );
        
      if( $data['idx_author'] != $user['id'] && !isAdmin($user['id']) )
        message_die( 'Vous ne pouvez modifier que vos extensions.' );
        
      // The form has been sent
      if( isset( $_POST['send'] ) )
      {
        $_POST = escape_array( $_POST );
        
        // Deletes categories relations, and creates them again
        $sql =  "DELETE FROM " . EXT_CAT_TABLE;
        $sql .= " WHERE idx_extension = '" . $_POST['extension_id'] . "'";
        $db->query( $sql ) or die( 'Erreur lors de la modification de l\'extension.' );
        
        foreach( $_POST['extension_category'] as $category )
        {
          $sql =  "INSERT INTO " . EXT_CAT_TABLE . " (idx_category, idx_extension)";
          $sql .= " VALUES ('" . $category . "', '" . $_POST['extension_id'] . "')";
          
          $db->query( $sql ) or die( 'Erreur ' . $sql );
        }
        
        // Updates the extension information
        $sql =  "UPDATE " . EXT_TABLE;
        $sql .= " SET name = '" . $_POST['extension_name'] . "', description = '"; 
        $sql .= $_POST['extension_description'] . "'";
        $sql .= " WHERE id_extension = '" . $id . "'";
        $db->query( $sql ) or die( 'Erreur ' . $sql );
        
        message_success( 'Extension modifiée avec succès.' );
      }
        
      $template->set_file( 'extension_mod', 'extension_mod.tpl' );
      
      // Gets the category listing
      $sql =  "SELECT name, id_category, idx_parent";
      $sql .= " FROM " . CAT_TABLE;
      $sql .= " ORDER BY name ASC";
      $req = $db->query( $sql );
      
      // We need to get the subcategories, and the categories
      $cats = array();
      $subcats = array();
      while( $data = $db->fetch_assoc( $req ) )
      {
        if( !empty( $data['idx_parent'] ) )
          $subcats[] = $data['idx_parent'];
          
        $cats[] = $data;
      }
        
      $sql =  "SELECT idx_category";
      $sql .= " FROM " . EXT_CAT_TABLE;
      $sql .= " WHERE idx_extension = '" . $id . "'";
      $req = $db->query( $sql );
      
      $ext_categories = array();
      while( $data = $db->fetch_assoc( $req ) )
        $ext_categories[] = $data['idx_category'];
        
      $sql =  "SELECT version, id_revision";
      $sql .= " FROM " . REV_TABLE;
      $sql .= " WHERE idx_extension = '" . $id . "'";
      $sql .= " ORDER BY version ASC";
      $req = $db->query( $sql );
      
      $revisions = array();
      while( $data = $db->fetch_assoc( $req ) )
        $revisions[] = $data;
        
      // Gets the information about the selected extension
      $sql =  "SELECT name, description";
      $sql .= " FROM " . EXT_TABLE;
      $sql .= " WHERE id_extension = '" . $id . "'";
      $req = $db->query( $sql );
      $data_ext = $db->fetch_assoc( $req );
      
      $template->set_block( 'extension_mod', 'extension_category', 'Textension_category' );
      // $template->set_block( 'extension_mod', 'extension_compatibility', 'Textension_compatibility' );
      $template->set_block( 'extension_mod', 'revision', 'Trevision' );
      
      // Fill in the categories listing
      foreach( $cats as $cat )
      {
        // This cat has no children, so we can display it
        if( !in_array( $cat['id_category'], $subcats ) )
        {
          $template->set_var( array( 'L_EXTENSION_CAT_NAME' => $cat['name'],
                                     'L_EXTENSION_CAT_VALUE' => $cat['id_category'] ) );
          if( in_array( $cat['id_category'], $ext_categories ) )
          {
            $template->set_var( 'L_EXTENSION_CAT_SELECTED', 'selected' );
          }
          else 
          {
            $template->set_var( 'L_EXTENSION_CAT_SELECTED', '' );
          }

          $template->parse( 'Textension_category', 'extension_category', true );
        }
      }
      
      foreach( $revisions as $revision )
      {
        $template->set_var( array( 'L_REVISION_VERSION' => $revision['version'],
                                   'L_REVISION_ID' => $revision['id_revision'] ) );
        $template->parse( 'Trevision', 'revision', true );
      }
      
      $template->set_var( array( 'L_EXTENSION_NAME' => $data_ext['name'],
                                 'L_EXTENSION_DESCRIPTION' => $data_ext['description'],
                                 'L_EXTENSION_ID' => $id ));
      
      build_header();
      $template->parse( 'output', 'extension_mod', true );
      build_footer();

      break;
    }
  }
  
  // Contributions listing
  $template->set_file( 'contributions', 'contributions.tpl' );
    
  $sql =  "SELECT COUNT(DISTINCT e.id_extension) as extensions_count";
  $sql .= " FROM " . EXT_TABLE . " e";
  if( isset( $_SESSION['id_version'] ) )
  {
    $sql .= " INNER JOIN " . REV_TABLE . " r ON r.idx_extension = e.id_extension";
    $sql .= " INNER JOIN " . COMP_TABLE . " rc ON rc.idx_revision = r.id_revision";
    $sql .= " AND rc.idx_version = '" . $_SESSION['id_version'] . "'";
  }
  $sql .= " WHERE e.idx_author = '" . $user['id'] . "'";

  $req = $db->query($sql);
  $data = $db->fetch_assoc($req);

  // Pages system managing 
  $extensions_count = $data['extensions_count'];
  
  $pages_count = ceil( $extensions_count / EXTENSIONS_PER_PAGE );
  if( $page > $pages_count )
    $page = $pages_count;
  
  if( $extensions_count == 0)
    message_die( 'Vous n\'avez proposé aucune extension pour l\'instant.<br />' . 
                 'Ou le filtre de version de vous permet pas de les voir.', 'Vos contributions', false );

  $extensions_start = ( ( $page - 1 ) * EXTENSIONS_PER_PAGE ) + 1;
  if( $page * EXTENSIONS_PER_PAGE > $extensions_count )
  {
    $extensions_end = $extensions_count;
  }
  else 
  {
    $extensions_end = $page * EXTENSIONS_PER_PAGE;
  }
  
  // Gets the total information about the extensions
  $sql =  "SELECT e.name, e.description, r.id_revision, e.id_extension";
  $sql .= " FROM pwg_revisions r ";
  $sql .= " INNER JOIN pwg_extensions e ON r.idx_extension = e.id_extension";
  $sql .= " AND e.idx_author = '" . $user['id'] . "'";
  $sql .= " INNER JOIN " .USERS_TABLE." u ON u.id = e.idx_author";
  if( isset( $_SESSION['id_version'] ) )
  {
    $sql .= " INNER JOIN pwg_revisions_compatibilities rc ON rc.idx_revision = r.id_revision";
    $sql .= " AND rc.idx_version = '" . intval( $_SESSION['id_version'] ) . "'";
  }
  $sql .= " GROUP BY e.id_extension";
  $sql .= " ORDER BY r.id_revision DESC";
  $sql .= " LIMIT " . ( ( $page - 1 ) * EXTENSIONS_PER_PAGE ) . "," . EXTENSIONS_PER_PAGE;

  $req = $db->query( $sql );
  
  $template->set_block('contributions', 'extension', 'Textension');
  while($data = $db->fetch_assoc($req))
  {            
    $template->set_var(
      array(
        'L_EXTENSION_NAME' => htmlspecialchars( strip_tags ( $data['name'] ) ),
        'L_EXTENSION_DESCRIPTION' => nl2br( htmlspecialchars( strip_tags( $data['description'] ) ) ),
        'L_EXTENSION_ID' => $data['id_extension']
        )
      );
    $template->parse('Textension', 'extension', true);
  }
  
  // Some other stuff for the pages managing
  if( $page > 1 )
  {
    $u_previous = '<a href="contributions.php?page=' . ( $page - 1 ) . '">&lt;&lt;</a> | ';
  }
  else 
  {
    $u_previous = '';
  }
  
  if( $page < $pages_count )
  {
    $u_next = ' | <a href="contributions.php?page=' . ( $page + 1 ) . '">&gt;&gt;</a>';
  }
  else 
  {
    $u_next = '';
  }
  
  $template->set_var(
    array(
      'L_EXTENSIONS_COUNT' => $extensions_count,
      'L_EXTENSIONS_START' => $extensions_start,
      'L_EXTENSIONS_END' => $extensions_end,
      'L_PAGE_ID' => $page,
      'L_PAGE_COUNT' => $pages_count,
      'U_PREVIOUS' => $u_previous,
      'U_NEXT' => $u_next,
      )
    );
    
  build_header();
  $template->parse( 'output', 'contributions', true );
  build_footer();
?>
