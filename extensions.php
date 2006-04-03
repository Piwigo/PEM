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

  // Action performed by the user
  switch( $action )
  {
    // Extension adding
    case 'add':
      if( !$pun_user['registered'] )
        message_die( 'Vous devez être connecté pour pouvoir accéder à cette section.' );
        
      // Form submitted
      if( isset( $_POST['send'] ) )
      {
echo '<pre>';
print_r($_FILES);
echo '</pre>';
        // Check file extension
        if( strtolower( substr( $_FILES['extension_file']['name'], -3) ) != 'zip' )
        {
          message_die( 'L\'extension du fichier n\'est pas correcte. Le fichier doit être un .zip !' );
        }

        // Check file existence
        if(file_exists(EXTENSIONS_DIR . $_FILES['extension_file']['name']))
          message_die( 'L\'extension proposée existe déjà.' );
          
        // Check file size
        if($_FILES['extension_file']['error'] == UPLOAD_ERR_INI_SIZE)
        {
          message_die( 'Le fichier est trop gros. Sa taille ne doit pas excéder ' . 
          ini_get( 'upload_max_filesize' ) . '.' );
        }

        // Checks that all the fields have been well filled
        if(empty( $_POST['extension_name'] ) or empty( $_POST['extension_description'] ) or 
           empty( $_POST['extension_version'] ) or empty( $_POST['extension_compatibility'] ) or 
           empty( $_POST['extension_category'] ))
        {
          message_die( 'Vous n\'avez pas rempli tous les champs.' );
        }
        
        // Escapes the array by using the mysql_escape_array( ) function
        $_POST = escape_array( $_POST );
          
        // Moves the file to its final destination
        move_uploaded_file( $_FILES['extension_file']['tmp_name'], EXTENSIONS_DIR . 
                            $_FILES['extension_file']['name'] );

        // Inserts the extension (need to be done before the other includes, to
        // retrieve the insert id
        $sql =  "INSERT INTO " . EXT_TABLE . " (idx_author, name, description)";
        $sql .= " VALUES ('" . $pun_user['id'] . "', '" . $_POST['extension_name'] . "', '";
        $sql .= $_POST['extension_description'] . "')";
        $db->query( $sql ) or message_die( 'Erreur durant l\'insertion de l\'extension. Contactez l\'administrateur' );
        $ext_id = $db->insert_id();
        
        // Inserts the extensions <-> categories link
        foreach( $_POST['extension_category'] as $category )
        {
          $sql =  "INSERT INTO " . EXT_CAT_TABLE . " (idx_category, idx_extension)";
          $sql .= " VALUES ('" . $category . "', '" . $ext_id . "')";
          $db->query( $sql ) or message_die( 'Erreur durant l\'insertion de l\'extension. Contactez l\'administrateur' );
        }
        
        // Inserts the revision
        $sql =  "INSERT INTO " . REV_TABLE . " (idx_extension, date, url, description, version)";
        $sql .= " VALUES ('" . $ext_id . "', '" . mktime() . "', '" . $_FILES['extension_file']['name'];
        $sql .= "', '', '" . $_POST['extension_version'] . "')";
        $db->query( $sql ) or die('Erreur durant l\'insertion de l\'extension. Contactez l\'administrateur' );
        $revision_id = $db->insert_id();
        
        // Inserts the revisions <-> compatibilities link
        foreach($_POST['extension_compatibility'] as $compatibility)
        {
          $sql =  "INSERT INTO " . COMP_TABLE . " (idx_revision, idx_version)";
          $sql .= " VALUES ('" . $revision_id . "', '" . $compatibility . "')";
          $db->query( $sql ) or die( 'Erreur durant l\'insertion de l\'extension. Contactez l\'administrateur' );
        }
        
        create_rss();
        message_success( 'L\'extension a été ajoutée avec succès. Merci de votre participation.', 'index.php' );
      }

      // Display the element adding form
      $template->set_file('extension_add', 'extension_add.tpl');
      
      // Get the category listing
      $sql =  "SELECT name, id_category, idx_parent";
      $sql .= " FROM " . CAT_TABLE;
      $sql .= " ORDER BY name ASC";
      $req = $db->query( $sql );
      
      // We need to display only categories that don't have sub-categories
      $cats = array();
      $subcats = array();
      while($data = $db->fetch_assoc($req))
      {
        if(!empty($data['idx_parent']))
          $subcats[] = $data['idx_parent'];
          
        $cats[] = $data;
      }
      
      // Display the cats
      $template->set_block('extension_add', 'extension_category', 'Textension_category');
      foreach($cats as $cat)
      {
        if(!in_array($cat['id_category'], $subcats))
        {
          $template->set_var(array( 'L_EXTENSION_CAT_NAME' => $cat['name'],
                                    'L_EXTENSION_CAT_VALUE' => $cat['id_category']));
          $template->parse('Textension_category', 'extension_category', true);
        }
      }
      
      // Get the PWG versions listing
      $sql =  "SELECT version, id_version";
      $sql .= " FROM " . VER_TABLE;
      $sql .= " ORDER BY version ASC";
      $req = $db->query($sql);
      
      // Display the available versions
      $template->set_block('extension_add', 'extension_compatibility', 'Textension_compatibility');
      while($data = $db->fetch_assoc($req))
      {
        $template->set_var(array( 'L_EXTENSION_COMP_VALUE' => $data['id_version'],
                                  'L_EXTENSION_COMP_NAME' => $data['version']));
        $template->parse('Textension_compatibility', 'extension_compatibility', true);
      }
        
      build_header();
      $template->parse('output', 'extension_add', true);
      build_footer();
      
    break;
  }
  
  // No action set, just display the extensions listing of the chosen category
  $template->set_file('extensions', 'extensions.tpl'); 
  
  // Get the category name
  $sql =  "SELECT name";
  $sql .= " FROM " . CAT_TABLE;
  $sql .= " WHERE id_category = '" . $id . "'";
  $req = $db->query( $sql );
  $data = $db->fetch_assoc( $req );
  
  if( $db->num_rows( $req ) == 0 )
    message_die( 'Cette catégorie n\'existe pas.', 'Erreur', false );
    
  $cat_name = $data['name'];
  
  // Get the extensions count for the selected category, used to display the
  // amount of pages
  $sql =  "SELECT COUNT(DISTINCT ec.idx_extension) as extensions_count";
  $sql .= " FROM " . EXT_TABLE . " e";
  $sql .= " INNER JOIN " . EXT_CAT_TABLE . " ec ON ec.idx_extension = e.id_extension";
  $sql .= " AND ec.idx_category = '" . $id . "'";
  if( isset( $_SESSION['id_version'] ) )
  {
    $sql .= " INNER JOIN " . REV_TABLE . " r ON r.idx_extension = e.id_extension";
    $sql .= " INNER JOIN " . COMP_TABLE . " rc ON rc.idx_revision = r.id_revision";
    $sql .= " AND rc.idx_version = '" . $_SESSION['id_version'] . "'";
  }
  $sql .= " GROUP BY ec.idx_category";

  $req = $db->query($sql);
  $data = $db->fetch_assoc($req);
  
  $extensions_count = $data['extensions_count'];
  $pages_count = ceil( $extensions_count / EXTENSIONS_PER_PAGE );
  
  // Selected page out of bounds, set it to the max
  if( $page > $pages_count )
    $page = $pages_count;
  
  // Set the beginning number of the displayed extensions
  $extensions_start = ( ( $page - 1 ) * EXTENSIONS_PER_PAGE ) + 1;
  
  if( $extensions_count == 0)
    message_die( 'Il n\'y a aucune extension dans cette catégorie.', $cat_name, false );
  
  // Calculate the end of the extensions
  if( $page * EXTENSIONS_PER_PAGE > $extensions_count )
  {
    $extensions_end = $extensions_count;
  }
  else 
  {
    $extensions_end = $page * EXTENSIONS_PER_PAGE;
  }
  
  // FInally, get the extensions listing
  $sql =  "SELECT e.name, u.username, e.description, MAX(r.version) AS version, r.id_revision,";
  $sql .= " e.id_extension, e.idx_author";
  $sql .= " FROM " . EXT_TABLE . " e";
  $sql .= " LEFT JOIN " . REV_TABLE . " r ON r.idx_extension = e.id_extension";
  $sql .= " INNER JOIN " . $db->prefix . "users u ON u.id = e.idx_author";
  $sql .= " INNER JOIN " . EXT_CAT_TABLE . " ct ON ct.idx_extension = e.id_extension";
  $sql .= " AND ct.idx_category = '" . $_GET['id'] . "'";
  if( isset( $_SESSION['id_version'] ) )
  {
    $sql .= " INNER JOIN " . COMP_TABLE . " ON idx_version = '" . 
            $_SESSION['id_version'] . "' AND idx_revision = r.id_revision";
  }
  $sql .= " GROUP BY e.id_extension";
  $sql .= " ORDER BY e.id_extension DESC";
  $sql .= " LIMIT " . ( ( $page - 1 ) * EXTENSIONS_PER_PAGE ) . "," . EXTENSIONS_PER_PAGE;

  $req = $db->query($sql);
  
  // Admin block used for admins and authors of the extension
  $template->set_block( 'extensions', 'switch_admin', 't_switch_admin' );
  $template->set_block('extensions', 'extension', 'Textension');
  
  // Display the extensions
  while($data = $db->fetch_assoc($req))
  {
    // Array containing the compatibilities for the current extension
    $comp_array = array();

    // Get the compatibility array (the compatibility for all the revisions of the extension)
    $sql =  "SELECT v.version";
    $sql .= " FROM " . REV_TABLE . " r";
    $sql .= " INNER JOIN " . EXT_TABLE . " e ON r.idx_extension = e.id_extension";
    $sql .= " INNER JOIN " . COMP_TABLE . " rc ON rc.idx_revision = r.id_revision";
    $sql .= " INNER JOIN " . VER_TABLE . " v ON v.id_version = rc.idx_version";
    $sql .= " WHERE e.id_extension = '" . $data['id_extension'] . "'";
    $sql .= " GROUP BY v.id_version";
    $sql .= " ORDER BY v.version ASC";
    
    $req_comp = $db->query($sql);
    
    while($data_comp = $db->fetch_assoc($req_comp))
      $comp_array[] = $data_comp['version'];
    
    $comp = implode(', ', $comp_array);
    $template->set_var(array( 'L_EXTENSION_NAME' => htmlspecialchars( strip_tags ( $data['name'] ) ),
                              'L_EXTENSION_VERSION' => htmlspecialchars( $data['version'] ),
                              'L_EXTENSION_AUTHOR' => htmlspecialchars( $data['username'] ),
                              'L_EXTENSION_DESCRIPTION' => nl2br( htmlspecialchars( strip_tags( $data['description'] ) ) ),
                              'L_EXTENSION_COMPATIBILITY' => $comp,
                              'L_EXTENSION_ID' => $data['id_extension'] ));
                              
    // Used to display the "Modifier / Supprimer" links
    if( isAdmin($pun_user['id']) || $pun_user['id'] == $data['idx_author'] )
    {
      $template->parse( 't_switch_admin', 'switch_admin' );
    }
    
    $template->parse('Textension', 'extension', true);
  }
  
  // Used to display whether or not the "previous" link
  if( $page > 1 )
  {
    $u_previous = '<a href="extensions.php?id=' . $id . '&amp;page=' . ( $page - 1 ) . '">&lt;&lt;</a> | ';
  }
  else 
  {
    $u_previous = '';
  }
  
  // Used to diplay whether or not the "next" link
  if( $page < $pages_count )
  {
    $u_next = ' | <a href="extensions.php?id=' . $id . '&amp;page=' . ( $page + 1 ) . '">&gt;&gt;</a>';
  }
  else 
  {
    $u_next = '';
  }
  
  $template->set_var(array( 'L_CATEGORY_NAME' => $cat_name,
                            'L_EXTENSIONS_COUNT' => $extensions_count,
                            'L_EXTENSIONS_START' => $extensions_start,
                            'L_EXTENSIONS_END' => $extensions_end,
                            'L_PAGE_ID' => $page,
                            'L_PAGE_COUNT' => $pages_count,
                            'U_PREVIOUS' => $u_previous,
                            'U_NEXT' => $u_next ));
  build_header();
  $template->parse('output', 'extensions', true);
  build_footer();
                              
  
  
?>
