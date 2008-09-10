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

function versort ($array)
{
  usort($array, 'version_compare');

  return $array;
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

function message_die($message, $title = 'Error', $go_back = true)
{
  global $root_path, $tpl, $db, $user, $page, $conf;
  
  $page['message'] = array(
    'title' => $title,
    'is_success' => false,
    'message' => $message,
    'go_back' => $go_back
    );
  include($root_path.'include/message.inc.php');
}

function message_success(
  $message,
  $redirect,
  $title = 'Success',
  $time_redirect = '5'
  )
{
  global $root_path, $tpl, $db, $user, $page, $conf;
  
  $page['message']['is_success'] = true;
  $page['message']['message'] = $message;
  $page['message']['redirect'] = $redirect;
  include($root_path.'include/message.inc.php');
}

function isAdmin($user_id)
{
  global $conf;

  return in_array($user_id, $conf['admin_users']);
}

function l10n($lang_key)
{
  global $conf;
  
  return $conf['l10n_key_prefix'].$lang_key;
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
 * updates multiple lines in a table
 *
 * @param string table_name
 * @param array dbfields
 * @param array datas
 * @return void
 */
function mass_updates($tablename, $dbfields, $datas)
{
  global $db;
  
  // depending on the MySQL version, we use the multi table update or N
  // update queries
  $query = 'SELECT VERSION() AS version;';
  list($mysql_version) = mysql_fetch_array($db->query($query));
  if (count($datas) < 10 or version_compare($mysql_version, '4.0.4') < 0)
  {
    // MySQL is prior to version 4.0.4, multi table update feature is not
    // available
    foreach ($datas as $data)
    {
      $query = '
UPDATE '.$tablename.'
  SET ';
      $is_first = true;
      foreach ($dbfields['update'] as $num => $key)
      {
        if (!$is_first)
        {
          $query.= ",\n      ";
        }
        $query.= $key.' = ';
        if (isset($data[$key]) and $data[$key] != '')
        {
          $query.= '\''.$data[$key].'\'';
        }
        else
        {
          $query.= 'NULL';
        }
        $is_first = false;
      }
      $query.= '
  WHERE ';
      foreach ($dbfields['primary'] as $num => $key)
      {
        if ($num > 1)
        {
          $query.= ' AND ';
        }
        $query.= $key.' = \''.$data[$key].'\'';
      }
      $query.= '
;';
      $db->query($query);
    }
  }
  else
  {
    // creation of the temporary table
    $query = '
SHOW FULL COLUMNS FROM '.$tablename.'
;';
    $result = $db->query($query);
    $columns = array();
    $all_fields = array_merge($dbfields['primary'], $dbfields['update']);
    while ($row = mysql_fetch_array($result))
    {
      if (in_array($row['Field'], $all_fields))
      {
        $column = $row['Field'];
        $column.= ' '.$row['Type'];
        if (!isset($row['Null']) or $row['Null'] == '')
        {
          $column.= ' NOT NULL';
        }
        if (isset($row['Default']))
        {
          $column.= " default '".$row['Default']."'";
        }
        if (isset($row['Collation']) and $row['Collation'] != 'NULL')
        {
          $column.= " collate '".$row['Collation']."'";
        }
        array_push($columns, $column);
      }
    }

    $temporary_tablename = $tablename.'_'.micro_seconds();

    $query = '
CREATE TABLE '.$temporary_tablename.'
(
'.implode(",\n", $columns).',
PRIMARY KEY ('.implode(',', $dbfields['primary']).')
)
;';
    $db->query($query);
    mass_inserts($temporary_tablename, $all_fields, $datas);
    // update of images table by joining with temporary table
    $query = '
UPDATE '.$tablename.' AS t1, '.$temporary_tablename.' AS t2
  SET '.
      implode(
        "\n    , ",
        array_map(
          create_function('$s', 'return "t1.$s = t2.$s";'),
          $dbfields['update']
          )
        ).'
  WHERE '.
      implode(
        "\n    AND ",
        array_map(
          create_function('$s', 'return "t1.$s = t2.$s";'),
          $dbfields['primary']
          )
        ).'
;';
    $db->query($query);
    $query = '
DROP TABLE '.$temporary_tablename.'
;';
    $db->query($query);
  }
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

function simple_hash_from_query($query, $keyname, $valuename)
{
  global $db;
  
  $array = array();

  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    $array[ $row[$keyname] ] = $row[$valuename];
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

    versort($versions_of[$revision_id]);
  }

  return $versions_of;
}

function get_version_ids_of_revision($revision_ids)
{
  if (count($revision_ids) == 0)
  {
    return array();
  }
  
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

    if (isset($revisions_of[$extension_id])) {
      foreach ($revisions_of[$extension_id] as $revision_id)
      {
        $version_ids_of_extension[$extension_id] = array_merge(
          $version_ids_of_extension[$extension_id],
          $version_ids_of_revision[$revision_id]
          );
      }
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
SELECT
    id_revision,
    version,
    date,
    idx_extension,
    description,
    url,
    accept_agreement
  FROM '.REV_TABLE.'
  WHERE id_revision IN ('.implode(',', $revision_ids).')
;';
  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result))
  {
    $revision_infos_of[ $row['id_revision'] ] = $row;
  }

  return $revision_infos_of;
}

function get_extension_infos_of($extension_ids)
{
  global $db;

  $extension_infos_of = array();

  $ids_string = '';
  if (is_array($extension_ids))
  {
    $ids_string = implode(',', $extension_ids);
  }
  else
  {
    $ids_string = $extension_ids;
  }
  
  $query = '
SELECT id_extension,
       name,
       idx_user,
       description
  FROM '.EXT_TABLE.'
  WHERE id_extension IN ('.$ids_string.')
;';
  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result))
  {
    if (is_array($extension_ids))
    {
      $extension_infos_of[ $row['id_extension'] ] = $row;
    }
    else
    {
      return $row;
    }
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
        .'<a href="'.$url.'1" rel="start" class="FirstActive">'
        .'&lt;&lt;first'
        .'</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="FirstInactive">&lt;&lt;first</span>';
    }

    // link on previous page ?
    if ($current_page > 1)
    {
      $previous = $current_page - 1;
      
      $pagination_bar.=
        "\n".'&nbsp;'
        .'<a href="'.$url.$previous.'" rel="prev" class="PrevActive">'
        .'&lt;prev'.'</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="PrevInactive">&lt;prev</span>';
    }

    $min_to_display = $current_page - $conf['paginate_pages_around'];
    $max_to_display = $current_page + $conf['paginate_pages_around'];
    $last_displayed_page = null;

    for ($page_number = 1; $page_number <= $nb_pages; $page_number++)
    {
      if ($page_number == 1
          or $page_number == $nb_pages
          or ($page_number >= $min_to_display
              and $page_number <= $max_to_display)
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
        '<a href="'.$url.$next.'" rel="next" class="NextActive">next&gt;</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="NextInactive">next&gt;</span>'
        ;
    }

    // link to last page?
    if ($current_page != $nb_pages)
    {
      $pagination_bar.=
        "\n".'&nbsp;'.
        '<a href="'.$url.$nb_pages.'" rel="last" class="LastActive">'
        .'last&gt;&gt;</a>'
        ;
    }
    else
    {
      $pagination_bar.=
        "\n".'&nbsp;<span class="LastInactive">last&gt;&gt;</span>';
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

/**
 * delete revisions and associated informations (version compatibilities)
 *
 * @param array of revision ids
 * @return void
 */
function delete_revisions($revision_ids)
{
  global $db;

  if (count($revision_ids) == 0)
  {
    return false;
  }

  $revision_infos_of = get_revision_infos_of($revision_ids);

  foreach ($revision_ids as $revision_id)
  {
    unlink(
      get_revision_src(
        $revision_infos_of[$revision_id]['idx_extension'],
        $revision_id,
        $revision_infos_of[$revision_id]['url']
        )
      );
  }

  $query = '
DELETE
  FROM '.COMP_TABLE.'
  WHERE idx_revision IN ('.implode(',', $revision_ids).')
;';
  $db->query($query);

  $query = '
DELETE
  FROM '.REV_TABLE.'
  WHERE id_revision IN ('.implode(',', $revision_ids).')
;';
  $db->query($query);
}

function get_extension_dir($extension_id)
{
  return EXTENSIONS_DIR.'extension-'.$extension_id;
}

function get_revision_src($extension_id, $revision_id, $url)
{
  return get_extension_dir($extension_id)
    .'/revision-'.$revision_id
    .'/'.$url
  ;
}

function get_extension_thumbnail_src($extension_id)
{
  return get_extension_dir($extension_id).'/thumbnail.jpg';
}

function get_extension_screenshot_src($extension_id)
{
  return get_extension_dir($extension_id).'/screenshot.jpg';
}

function get_extension_screenshot_infos($extension_id)
{
  $thumbnail_src  = get_extension_thumbnail_src($extension_id);
  $screenshot_src = get_extension_screenshot_src($extension_id);
  
  if (is_file($thumbnail_src) and is_file($screenshot_src))
  {
    return array(
      'thumbnail_src'  => $thumbnail_src,
      'screenshot_url' => $screenshot_src,
      );
  }
  else
  {
//     return array(
//       'thumbnail_src'  => './default_thumbnail.jpg',
//       'screenshot_url' => 'http://le-gall.net/pierrick',
//       );
   return false;
  }
}

function print_array($array)
{
  echo '<pre>';
  print_r($array);
  echo '</pre>';
}

// get_boolean transforms a string to a boolean value. If the string is
// "false" (case insensitive), then the boolean value false is returned. In
// any other case, true is returned.
function get_boolean($string, $default = true)
{
  $boolean = $default;
  
  if (preg_match('/^false$/i', $string))
  {
    $boolean = false;
  }

  if (preg_match('/^true$/i', $string))
  {
    $boolean = true;
  }
  
  return $boolean;
}

function log_download($revision_id)
{
  global $db;
  
  $revision_infos_of = get_revision_infos_of(array($revision_id));

  if (count($revision_infos_of) == 0) {
    return false;
  }

  $query = '
SELECT CURDATE()
;';
  list($curdate) = mysql_fetch_row($db->query($query));
  list($curyear, $curmonth, $curday) = explode('-', $curdate);

  $query = '
INSERT INTO '.DOWNLOAD_LOG_TABLE.'
  (
    year,
    month,
    day,
    IP,
    idx_revision
  )
  VALUES
  (
    '.$curyear.',
    '.$curmonth.',
    '.$curday.',
    \''.$_SERVER['REMOTE_ADDR'].'\',
    '.$revision_id.'
  )
;';
  $db->query($query);
}

function get_download_of_extension($extension_ids) {
  global $db;

  if (count($extension_ids) == 0) {
    return array();
  }

  $download_of_extension = array();

  foreach ($extension_ids as $id) {
    $download_of_extension[$id] = 0;
  }
  
  $query = '
SELECT
    idx_extension AS extension_id,
    count(*) AS counter
  FROM '.DOWNLOAD_LOG_TABLE.'
    JOIN '.REV_TABLE.' ON id_revision = idx_revision
  WHERE idx_extension IN ('.implode(',', $extension_ids).')
  GROUP BY idx_extension
;';

  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result)) {
    $downloads_of_extension[ $row['extension_id'] ] = $row['counter'];
  }

  return $downloads_of_extension;
}

function compare_username($a, $b) {
  return strcmp(strtolower($a["username"]), strtolower($b["username"]));
}
?>
