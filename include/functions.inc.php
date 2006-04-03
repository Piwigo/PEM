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


/***************************************************************************************************
* @descr :  Builds the page header, with the menu
* @param :  $parse boolean
* @return : -
* @author : Sephi
***************************************************************************************************/
function build_header( $parse = true )
{  
  global $template;
  global $db;
  global $pun_user;
  
  // Get the extensions count
  $sql =  "SELECT COUNT(id_extension) AS extensions_count";
  $sql .= " FROM " . EXT_TABLE;
  $req = $db->query( $sql );
  $data = $db->fetch_assoc( $req );
  $extensions_count = $data['extensions_count'];
  
  // Get the left nav menu
  $sql =  "SELECT id_category, idx_parent, name, description";
  $sql .= " FROM " . CAT_TABLE;
  $sql .= " ORDER BY idx_parent ASC, name ASC";
  $req = $db->query( $sql );
  
  $categories = array();
  while( $data = $db->fetch_assoc( $req ) )
    $categories[] = $data;

  $template->set_file( 'header', 'header.tpl' );
  
  $template->set_block( 'header', 'category_sublevel_item', 't_category_sublevel_item' );
  $template->set_block( 'header', 'category_sublevel', 't_category_sublevel' );
  $template->set_block( 'header', 'category', 't_category' );
  
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
        $template->parse('t_category_sublevel_item', 'category_sublevel_item', true);
        $has_sublevels = true;
      }
    }
    
    // Doesn't display a link on the category if it has children
    if($has_sublevels)
    {
      $cat_name = $cat['name'];
    }
    else 
    {
      $cat_name = '<a href="extensions.php?id=' . $cat['id_category'] . '">' . $cat['name'] . '</a>';
    }
    
    $template->set_var( array ( 'U_CATEGORY' => $cat_name,
                                'L_CATEGORY_DESCRIPTION' => $cat['description'] ) );
                                
    
    if( $has_sublevels )
      $template->parse( 't_category_sublevel', 'category_sublevel', true );
      
    $template->parse( 't_category', 'category', true );
    $template->clear_var( 't_category_sublevel' );
    $template->clear_var( 't_category_sublevel_item' );
  }
  
  // Gets the list of the available PWG versions (allows users to filter)
  $sql =  "SELECT id_version, version";
  $sql .= " FROM " . VER_TABLE;
  $sql .= " ORDER BY version DESC";
  $req = $db->query($sql);
  
  $template->set_block( 'header', 'pwg_version', 't_pwg_version' );
  
  // Displays the versions
  while( $data = $db->fetch_assoc( $req ) )
  {
    $template->set_var( array( 'L_PWG_VERSION_ID' => $data['id_version'],
                               'L_PWG_VERSION_NAME' => $data['version'] ) );
                               
    if( isset( $_SESSION['id_version'] ) )
    {
      if( $_SESSION['id_version'] == $data['id_version'] )
      {
        $template->set_var( 'L_PWG_VERSION_SELECTED', 'selected' );
      }
      else 
      {
        $template->set_var( 'L_PWG_VERSION_SELECTED', '' );
      }
    }
    
    $template->parse( 't_pwg_version', 'pwg_version', true );
  }
  
  $template->set_var(array( 'L_EXTENSIONS_TOTAL_COUNT' => $extensions_count,
                            'PUN_ROOT' => PUN_ROOT,
                            'L_REQUEST_URI' => $_SERVER['REQUEST_URI'] ));
  
  $template->set_block( 'header', 'user_not_logged_in', 't_user_not_logged_in' );
  $template->set_block( 'header', 'user_logged_in', 't_user_logged_in' );
  
  // Display the user menu
  if( $pun_user['registered'] )
    $template->parse( 't_user_logged_in', 'user_logged_in' );
  else
    $template->parse( 't_user_not_logged_in', 'user_not_logged_in' );
    
  if( $parse )
    $template->parse('output', 'header');
}    

function build_footer()
{
  global $template;
  
  $template->set_file('footer', 'footer.tpl');
  $template->parse('output', 'footer', true);
  $template->p('output');
  exit();
}

function message_success($message, $redirect = '', $title = 'Succès', $time_redirect = '5')
{
  global $template;
  
  build_header(false);
  $template->set_file('message', 'message.tpl');
  $template->set_var(array( 'L_MESSAGE_TITLE' => $title,
                            'L_MESSAGE_TEXT' => $message,
                            'L_META' => '<meta http-equiv="refresh" content="' . 
                                        $time_redirect . ';' . $redirect . '">'));
  $template->set_block('message', 'switch_redirect', 'Tswitch_redirect'); 
  $template->set_block('message', 'switch_goback', 'Tswitch_goback');     
  if(!empty($redirect))
  {
    $template->set_var(array( 'L_TIME_REDIRECT' => $time_redirect,
                              'U_REDIRECT' => $redirect));
    $template->parse('Tswitch_redirect', 'switch_redirect');
  }
  $template->parse('output', 'header');
  $template->parse('output', 'message', true);
  build_footer();
}

function message_die($message, $title = 'Erreur', $go_back = true)
{
  global $template;
  
  build_header();
  $template->set_file('message', 'message.tpl');
  $template->set_var(array( 'L_MESSAGE_TITLE' => $title,
                            'L_MESSAGE_TEXT' => $message));
  $template->set_block('message', 'switch_redirect', 'Tswitch_redirect');
  $template->set_block('message', 'switch_goback', 'Tswitch_goback'); 
  
  if( $go_back )
  {
    $template->parse('Tswitch_goback', 'switch_goback');
  }
  
  $template->parse('output', 'message', true);
  build_footer();
}

function escape_array($array_to_escape)
{
  foreach($array_to_escape as $key => $element)
  {
    if(!is_array($element))
      $array_to_escape[$key] = mysql_escape_string($element);
  }
  
  return $array_to_escape;
}

/***************************************************************************************************
* @descr :  Returns the version (format xx.yy.zz) calculated from a 6 bits integer
* @param :  int $version
* @return : string
* @author : Sephi
* @deprecated
***************************************************************************************************/
function getVersion($version)
{
  $major = ($version & 0xFF0000) >> 16;
  $prior = ($version & 0x00FF00) >> 8;
  $rev = ($version & 0x0000FF);
  
  return $major . '.' . $prior . '.' . $rev;
}

/***************************************************************************************************
* @descr :  Returns the version in a 6 bits integer, calculated from a string (xx.yy.zz) 
* @param :  string $version
* @return : int
* @author : Sephi
* @deprecated
***************************************************************************************************/
function buildVersion($version)
{
  $tblVersion = explode('.', $version);
  $major = str_pad(dechex($tblVersion[0]), 2, 0, STR_PAD_LEFT);
  $prior = str_pad(dechex($tblVersion[1]), 2, 0, STR_PAD_LEFT);
  $rev = str_pad(dechex($tblVersion[2]), 2, 0, STR_PAD_LEFT);
  return hexdec($major . $prior . $rev);
}

function create_rss()
{
  global $template;
  global $root_path;
  global $db;
  
  // Gets the lastest X (defined in constants.inc.php) mods information
  $sql =  "SELECT e.name, u.username, e.description, r.version AS version, r.id_revision,";
  $sql .= " e.id_extension, e.idx_author";
  $sql .= " FROM " . REV_TABLE . " r";
  $sql .= " INNER JOIN " . EXT_TABLE . " e ON r.idx_extension = e.id_extension";
  $sql .= " INNER JOIN " . $db->prefix . "users u ON u.id = e.idx_author";
  $sql .= " ORDER BY r.id_revision DESC";
  $sql .= " LIMIT 0," . LAST_ADDED_EXTS_COUNT;
  
  $req = $db->query( $sql );
  
  $template->set_file( 'rss_extensions', 'rss_extensions.tpl' );
  $template->set_block( 'rss_extensions', 'extension', 't_extension' );
  while( $data = $db->fetch_assoc( $req ) )
  {
    $path = pathinfo( $root_path . 'index.php' );
    
    $template->set_var( array( 'L_EXTENSION_NAME' => $data['name'],
                               'U_EXTENSION' => 'http://' . $_SERVER['SERVER_NAME'] . ROOT . 
                                                'view_extension.php?id=' . $data['id_extension'],
                               'L_EXTENSION_AUTHOR' => $data['username'],
                               'L_EXTENSION_VERSION' => $data['version'],
                               'L_EXTENSION_DESCRIPTION' => $data['description'] ) );
    $template->parse( 't_extension', 'extension', true );
  }
  
  $template->parse( 'output', 'rss_extensions' );
  $fp = fopen( $root_path . 'extensions.rss', 'w' );
  fputs( $fp, $template->get('output') );
  fclose( $fp );
}

function isAdmin($user_id)
{
  global $conf;

  return in_array($user_id, $conf['admin_users']);
}

?>
