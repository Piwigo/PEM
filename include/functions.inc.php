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
  global $user;
  global $conf;
  
  // Get the left nav menu
  $query = '
SELECT id_category,
       idx_parent,
       name,
       description
  FROM '.CAT_TABLE.'
  ORDER BY name ASC
;';
  $req = $db->query($query);
  
  $categories = array();
  while ($data = $db->fetch_assoc($req))
  {
    array_push($categories, $data);
  }

  $template->set_file( 'header', 'header.tpl' );
  
  $template->set_block( 'header', 'category', 't_category' );
  
  // Browse the categories and display them
  foreach($categories as $cat)
  {
    $template->set_var(
      array(
        'URL' => 'extensions.php?category='.$cat['id_category'],
        'NAME' => $cat['name'],
        )
      );
      
    $template->parse('t_category', 'category', true);
  }
  
  // Gets the list of the available PWG versions (allows users to filter)
  $query = '
SELECT id_version,
       version
  FROM '.VER_TABLE.'
  ORDER BY version DESC
;';
  $req = $db->query($query);
  
  $template->set_block('header', 'pwg_version', 't_pwg_version');
  
  // Displays the versions
  while ($data = $db->fetch_assoc($req))
  {
    $template->set_var(
      array(
        'L_PWG_VERSION_ID' => $data['id_version'],
        'L_PWG_VERSION_NAME' => $data['version'],
        )
      );
                               
    if (isset($_SESSION['id_version']))
    {
      if ($_SESSION['id_version'] == $data['id_version'])
      {
        $template->set_var('L_PWG_VERSION_SELECTED', 'selected="selected"');
      }
    }
    
    $template->parse('t_pwg_version', 'pwg_version', true);
  }
  
  $template->set_var(
    array(
      'PAGE_TITLE' => $conf['page_title'],
      'L_REQUEST_URI' => $_SERVER['REQUEST_URI']
      )
    );
  
  $template->set_block( 'header', 'user_not_logged_in', 't_user_not_logged_in' );
  $template->set_block( 'header', 'user_logged_in', 't_user_logged_in' );
  
  // Display the user menu
  if (isset($user['id']))
  {
    $template->set_var(
      array(
        'USERNAME' => $user['username'],
        )
      );
    $template->parse( 't_user_logged_in', 'user_logged_in' );
  }
  else
  {
    $template->parse( 't_user_not_logged_in', 'user_not_logged_in' );
  }
    
  if ($parse)
  {
    $template->parse('output', 'header');
  }
}    

function build_footer()
{
  global $template, $t2;
  
  $template->set_file('footer', 'footer.tpl');
  $template->parse('output', 'footer', true);
  $template->p('output');

  echo get_elapsed_time($t2, get_moment());
  exit();
}

function message_success($message, $redirect = '', $title = 'Succès', $time_redirect = '5')
{
  global $template;
  
  build_header(false);
  $template->set_file('message', 'message.tpl');
  $template->set_var(
    array(
      'L_MESSAGE_TITLE' => $title,
      'L_MESSAGE_TEXT' => $message,
      'L_META' =>
        '<meta http-equiv="refresh"'
        .' content="'.$time_redirect . ';' . $redirect . '">',
      )
    );
  $template->set_block('message', 'switch_redirect', 'Tswitch_redirect'); 
  $template->set_block('message', 'switch_goback', 'Tswitch_goback');     
  if (!empty($redirect))
  {
    $template->set_var(
      array(
        'L_TIME_REDIRECT' => $time_redirect,
        'U_REDIRECT' => $redirect
        )
      );
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
  $template->set_var(
    array(
      'L_MESSAGE_TITLE' => $title,
      'L_MESSAGE_TEXT' => $message
      )
    );
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

function l10n($lang_key)
{
  return '{l10n}'.$lang_key;
}

/**
 * inserts multiple lines in a table
 *
 * @param string table_name
 * @param array dbfields
 * @param array inserts
 * @return void
 */
function mass_inserts($table_name, $dbfields, $datas)
{
  global $db;
  
  $query = '
INSERT INTO '.$table_name.'
  ('.implode(',', $dbfields).')
   VALUES';
  foreach ($datas as $insert_id => $insert)
  {
    $query.= '
  ';
    if ($insert_id > 0)
    {
      $query.= ',';
    }
    $query.= '(';
    foreach ($dbfields as $field_id => $dbfield)
    {
      if ($field_id > 0)
      {
        $query.= ',';
      }

      if (!isset($insert[$dbfield]) or $insert[$dbfield] == '')
      {
        $query.= 'NULL';
      }
      else
      {
        $query.= "'".$insert[$dbfield]."'";
      }
    }
    $query.=')';
  }
  $query.= '
;';
  
  $db->query($query);
}

/**
 * creates an array based on a query, this function is a very common pattern
 * used here
 *
 * @param string $query
 * @param string $fieldname
 * @return array
 */
function array_from_query($query, $fieldname)
{
  global $db;
  
  $array = array();

  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    array_push($array, $row[$fieldname]);
  }

  return $array;
}

function get_version_name_of()
{
  global $db;

  $version_name_of = array();

  $query = '
SELECT id_version,
       version
  FROM '.VER_TABLE.'
;';
  $result = $db->query($query);

  while ($row = $db->fetch_array($result))
  {
    $version_name_of[ $row['id_version'] ] = $row['version'];
  }

  return $version_name_of;
}

function get_versions_of_revision($revision_ids)
{
  $versions_of = array();
  $version_ids_of = get_version_ids_of_revision($revision_ids);
  $version_name_of = get_version_name_of();

  foreach ($revision_ids as $revision_id)
  {
    $versions_of[$revision_id] = array();

    foreach ($version_ids_of[$revision_id] as $version_id)
    {
      array_push(
        $versions_of[$revision_id],
        $version_name_of[$version_id]
        );
    }

    natcasesort($versions_of[$revision_id]);
  }

  return $versions_of;
}

function get_version_ids_of_revision($revision_ids)
{
  global $db;

    // Get list of compatibilities
  $version_ids_of = array();
  
  $query = '
SELECT idx_version,
       idx_revision
  FROM '.COMP_TABLE.'
  WHERE idx_revision IN ('.implode(',', $revision_ids).')
;';
  
  $result = $db->query($query);
  
  while ($row = $db->fetch_array($result))
  {
    if (!isset($version_ids_of[ $row['idx_revision'] ]))
    {
      $version_ids_of[ $row['idx_revision'] ] = array();
    }
    
    array_push(
      $version_ids_of[ $row['idx_revision'] ],
      $row['idx_version']
      );
  }

  return $version_ids_of;
}

function get_version_ids_of_extension($extension_ids)
{
  global $db;
  
  // first we find the revisions associated to each extension
  $query = '
SELECT id_revision,
       idx_extension
  FROM '.REV_TABLE.'
  WHERE idx_extension IN ('.implode(',', $extension_ids).')
;';

  $revision_ids = array();
  $revisions_of = array();

  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    // add the revision id to the list of all revisions
    array_push($revision_ids, $row['id_revision']);

    // add the revision id to the list of revision to a particular extension.
    if (!isset($revisions_of[ $row['idx_extension'] ]))
    {
      $revisions_of[ $row['idx_extension'] ] = array();
    }
    array_push(
      $revisions_of[ $row['idx_extension'] ],
      $row['id_revision']
      );
  }

  $version_ids_of_revision = get_version_ids_of_revision($revision_ids);
  $version_ids_of_extension = array();

  foreach ($extension_ids as $extension_id)
  {
    $version_ids_of_extension[$extension_id] = array();

    foreach ($revisions_of[$extension_id] as $revision_id)
    {
      $version_ids_of_extension[$extension_id] = array_merge(
        $version_ids_of_extension[$extension_id],
        $version_ids_of_revision[$revision_id]
        );
    }

    $version_ids_of_extension[$extension_id] =
      array_unique($version_ids_of_extension[$extension_id]);
  }

  return $version_ids_of_extension;
}

function get_versions_of_extension($extension_ids)
{
  $versions_of = array();
  $version_ids_of = get_version_ids_of_extension($extension_ids);
  $version_name_of = get_version_name_of();

  foreach ($extension_ids as $extension_id)
  {
    $versions_of[$extension_id] = array();

    foreach ($version_ids_of[$extension_id] as $version_id)
    {
      array_push(
        $versions_of[$extension_id],
        $version_name_of[$version_id]
        );
    }

    natcasesort($versions_of[$extension_id]);
  }

  return $versions_of;
}

// The function get_elapsed_time returns the number of seconds (with 3
// decimals precision) between the start time and the end time given.
function get_elapsed_time( $start, $end )
{
  return number_format( $end - $start, 3, '.', ' ').' s';
}

// The function get_moment returns a float value coresponding to the number
// of seconds since the unix epoch (1st January 1970) and the microseconds
// are precised : e.g. 1052343429.89276600
function get_moment()
{
  $t1 = explode( ' ', microtime() );
  $t2 = explode( '.', $t1[0] );
  $t2 = $t1[1].'.'.$t2[1];
  return $t2;
}

function get_revision_infos_of($revision_ids)
{
  global $db;

  $revision_infos_of = array();
  
  // retrieve revisions information
  $query = '
SELECT id_revision,
       version,
       date,
       idx_extension,
       description,
       url
  FROM '.REV_TABLE.'
  WHERE id_revision IN ('.implode(',', $revision_ids).')
;';
  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    $revision_infos_of[ $row['id_revision'] ] = $row;
  }

  return $revision_infos_of;
}

function get_extension_infos_of($extension_ids)
{
  global $db;

  $extension_infos_of = array();
  
  $query = '
SELECT id_extension,
       name,
       idx_author,
       description
  FROM '.EXT_TABLE.'
  WHERE id_extension IN ('.implode(',', $extension_ids).')
;';
  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result))
  {
    $extension_infos_of[ $row['id_extension'] ] = $row;
  }

  return $extension_infos_of;
}

function array_from_subfield($hash, $field)
{
  $array = array();
  
  foreach ($hash as $row)
  {
    array_push($array, $row[$field]);
  }

  return $array;
}

function create_pagination_bar(
  $base_url, $nb_pages, $current_page, $param_name
  )
{
  global $conf;

  $url =
    $base_url.
    (preg_match('/\?/', $base_url) ? '&amp;' : '?').
    $param_name.'='
    ;

  $pagination_bar = '';

  // current page detection
  if (!isset($current_page)
      or !is_numeric($current_page)
      or $current_page < 0)
  {
    $current_page = 1;
  }

  // navigation bar useful only if more than one page to display !
  if ($nb_pages > 1)
  {
    // link to first page?
    if ($current_page > 1)
    {
      $pagination_bar.=
        "\n".'&nbsp;'
        .'<a href="'.$url.'1" rel="start">'
        .'&lt;&lt;'
        .'</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="inactive">&lt;&lt;</span>';
    }

    // link on previous page ?
    if ($current_page > 1)
    {
      $previous = $current_page - 1;
      
      $pagination_bar.=
        "\n".'&nbsp;'
        .'<a href="'.$url.$previous.'" rel="prev">'.'&lt;'.'</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="inactive">&lt;</span>';
    }

    $min_to_display = $current_page - $conf['paginate_pages_around'];
    $max_to_display = $current_page + $conf['paginate_pages_around'];
    $last_displayed_page = null;

    for ($page_number = 1; $page_number <= $nb_pages; $page_number++)
    {
      if ($page_number == 1
          or $page_number == $nb_pages
          or ($page_number >= $min_to_display and $page_number <= $max_to_display)
        )
      {
        if (isset($last_displayed_page)
            and $last_displayed_page != $page_number - 1
          )
        {
          $pagination_bar.=
            "\n".'&nbsp;<span class="inactive">...</span>'
            ;
        }
        
        if ($page_number == $current_page)
        {
          $pagination_bar.=
            "\n".'&nbsp;'
            .'<span class="currentPage">'.$page_number.'</span>'
            ;
        }
        else
        {
          $pagination_bar.=
            "\n".'&nbsp;'
            .'<a href="'.$url.$page_number.'">'.$page_number.'</a>'
            ;
        }
        $last_displayed_page = $page_number;
      }
    }
    
    // link on next page?
    if ($current_page < $nb_pages)
    {
      $next = $current_page + 1;
      
      $pagination_bar.=
        "\n".'&nbsp;'.
        '<a href="'.$url.$next.'" rel="next">&gt;</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="inactive">&gt;</span>'
        ;
    }

    // link to last page?
    if ($current_page != $nb_pages)
    {
      $pagination_bar.=
        "\n".'&nbsp;'.
        '<a href="'.$url.$nb_pages.'" rel="last">&gt;&gt;</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="inactive">&gt;&gt;</span>';
    }
  }
  
  return $pagination_bar;
}

function get_extension_ids_without_revision()
{
  $query = '
SELECT id_extension
  FROM '.EXT_TABLE.'
;';
  $all_extension_ids = array_from_query($query, 'id_extension');

  $query = '
SELECT DISTINCT idx_extension
  FROM '.REV_TABLE.'
;';
  $non_empty_extension_ids = array_from_query($query, 'idx_extension');

  return array_diff($all_extension_ids, $non_empty_extension_ids);
}

/**
 * Returns the number of pages to display in a pagination bar, given the number
 * of items and the number of items per page.
 *
 * @param int number of items
 * @param int number of items per page
 * @return int
 */
function get_nb_pages($nb_items, $nb_items_per_page)
{
  return intval(($nb_items - 1) / $nb_items_per_page) + 1;
}
?>
