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

  define( 'INTERNAL', true );
  $root_path = './';
  require_once( $root_path . 'include/common.inc.php' );
  
  $id = isset( $_GET['id'] ) ? abs( intval( $_GET['id'] ) ) : '';
  
  // Gets extension informations
  $sql =  "SELECT description, username, name, idx_author";
  $sql .= " FROM " . EXT_TABLE;
  $sql .= " INNER JOIN " . $db->prefix . "users ON id = idx_author";
  $sql .= " WHERE id_extension = '" . $id . "'";
  $req = $db->query($sql);
  $data = $db->fetch_assoc( $req );
  
  if( $db->num_rows( $req ) == 0 )
    message_die( 'L\'extension désirée n\'existe pas.', 'Erreur', false );

  $template->set_file( 'view_extension', 'view_extension.tpl' );
  $template->set_block( 'view_extension', 'switch_admin', 't_switch_admin' );
  
  $template->set_var(array( 'L_EXTENSION_NAME' => htmlspecialchars ( strip_tags ( $data['name'] ) ),
                            'L_EXTENSION_DESCRIPTION' => nl2br( htmlspecialchars ( strip_tags ( $data['description'] ) ) ),
                            'L_EXTENSION_AUTHOR' => htmlspecialchars( $data['username'] ),
                            'L_EXTENSION_ID' => $id ) );
                            
  if( isAdmin($pun_user['id']) || $pun_user['id'] == $data['idx_author'] )
  {
    $template->parse( 't_switch_admin', 'switch_admin' );
  }

  // Gets the compatibilities array and the changelogs
  $sql =  "
SELECT v.version AS compatibility
     , r.version
     , r.url
     , r.description
    , r.id_revision
  FROM " . REV_TABLE . " r
    INNER JOIN " . EXT_TABLE . " e ON r.idx_extension = e.id_extension
    INNER JOIN " . COMP_TABLE . " rc ON rc.idx_revision = r.id_revision";

  if( isset( $_SESSION['id_version'] ) )
  {
    $sql .= "
      AND rc.idx_version = '" . intval( $_SESSION['id_version'] ) . "'";
  }

  $sql .= "
    INNER JOIN " . VER_TABLE . " v ON v.id_version = rc.idx_version
  WHERE e.id_extension = '" . $id . "'
  ORDER BY v.version DESC, r.version DESC
;";

  $req = $db->query( $sql );
  $revisions_available = $db->num_rows( $req );
  
  $template->set_block( 'view_extension', 'revision', 'Trevision' );
  $template->set_block( 'view_extension', 'revision_changelog', 'Trevision_changelog' );
  $template->set_block( 'view_extension', 'switch_no_rev', 'Tswitch_no_rev' );
  
  $revisions = array();
  $revisions_printed = array();
  
  while( $data = $db->fetch_assoc( $req ) )
  {
    $revisions[] = $data;
  }
  
  foreach( $revisions as $revision )
  {
    $template->set_var(
      array(
        'L_REVISION_COMPATIBILITY' => $revision['compatibility'],
        'L_REVISION_VERSION' => $revision['version'],
        'L_REVISION_ID' => $revision['id_revision'],
        'U_REVISION_DOWNLOAD' => EXTENSIONS_DIR . $revision['url']
        )
      );
    
    $template->parse( 'Trevision', 'revision', true );
    
    if( !in_array( $revision['id_revision'], $revisions_printed ) )
    {
      $template->set_var(
        array(
          'L_REVISION_CHANGELOG' => nl2br(
            htmlspecialchars(
              $revision['description']
              )
            ),
          'L_CHANGELOG_REVISION_VERSION' => htmlspecialchars(
            $revision['version']
            )
          )
        );
      
      $template->parse( 'Trevision_changelog', 'revision_changelog', true );
      $revisions_printed[] = $revision['id_revision'];
    }
  }
  
  if( $revisions_available == 0 )
  {
    $template->parse( 'Tswitch_no_rev', 'switch_no_rev' );
  }

  build_header(); 
  $template->parse('output', 'view_extension', true);
  build_footer();
?>
    
