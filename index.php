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

  $template->set_file('index', 'index.tpl');
  
  // Gets the lastest X (defined in constants.inc.php) extensions information
  $sql =  "SELECT e.name, u.username, e.description, r.version AS version, r.id_revision,";
  $sql .= " e.id_extension, e.idx_author";
  $sql .= " FROM " . REV_TABLE . " r";
  $sql .= " INNER JOIN " . EXT_TABLE . " e ON r.idx_extension = e.id_extension";
  $sql .= " INNER JOIN " . $db->prefix . "users u ON u.id = e.idx_author";
  if( isset( $_SESSION['id_version'] ) )
  {
    $sql .= " INNER JOIN " . COMP_TABLE . " rc ON rc.idx_revision = r.id_revision";
    $sql .=  " AND rc.idx_version = '" . intval( $_SESSION['id_version'] ) . "'";
  }
  $sql .= " ORDER BY r.id_revision DESC";
  $sql .= " LIMIT 0," . LAST_ADDED_EXTS_COUNT;

  $req = $db->query($sql);
  
  if( $db->num_rows( $req ) == 0 )
  {
    message_die( 'Aucune extension pour le moment. Essayez de changer le filtre de version.', 
                  LAST_ADDED_EXTS_COUNT . ' dernières extensions ajoutées', false );
  }
  
  // $template->set_block( 'index', 'switch_admin', 't_switch_admin' );
  $template->set_block('index', 'extension', 'Textension');
  
  // Displays the x last extensions 
  while($data = $db->fetch_assoc($req))
  {
    // Compatibilities array
    $comp = array();
    
    // Gets all the compatibilities of the current extension
    $sql =  "SELECT version AS compatibility";
    $sql .= " FROM " . COMP_TABLE;
    $sql .= " INNER JOIN " . VER_TABLE . " ON id_version = idx_version";
    $sql .= " WHERE idx_revision = '" . $data['id_revision'] . "'";
    $sql .= " ORDER BY version ASC";
    $req_comp = $db->query($sql);
    
    while($data_comp = $db->fetch_assoc($req_comp))
      $comp[] = $data_comp['compatibility'];

    $template->set_var(array( 'L_EXTENSION_NAME' => $data['name'],
                              'L_EXTENSION_AUTHOR' => $data['username'],
                              'L_EXTENSION_DESCRIPTION' => nl2br( htmlspecialchars( strip_tags( $data['description'] ) ) ),
//                              'U_EXTENSION' => EXTENSIONS_DIR . $data['url'],
                              'L_EXTENSION_COMPATIBILITY' => implode(', ', $comp),
                              'L_EXTENSION_VERSION' => $data['version'],
                              'L_EXTENSION_ID' => $data['id_extension'] ));

    // Used to display the "Modifier / Supprimer" link
    if( isAdmin($pun_user['id']) || $pun_user['id'] == $data['idx_author'] )
    {
      // $template->parse( 't_switch_admin', 'switch_admin' );
    }
    
    $template->parse('Textension', 'extension', true);
    $template->clear_var( 't_switch_admin' );
  }
  
  $template->set_var('L_LAST_ADDED_EXTS_COUNT', LAST_ADDED_EXTS_COUNT);
  
  build_header();
  $template->parse('output', 'index', true);
  build_footer();
?>
