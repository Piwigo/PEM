<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2009 PEM Team - http://home.gna.org/pem            |
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

function versort($array)
{
  if (is_array($array[0])) {
    usort($array, 'pem_version_compare');
  }
  else {
    usort($array, 'version_compare');
  }

  return $array;
}

function pem_version_compare($a, $b)
{
  return version_compare($a['version'], $b['version']);
}

function message_die($message, $title = 'Error', $go_back = true)
{
  global $root_path, $tpl, $db, $user, $page, $conf;
  
  $page['message'] = array(
    'title' => l10n($title),
    'is_success' => false,
    'message' => l10n($message),
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
  
  $page['message']['title'] = l10n($title);
  $page['message']['is_success'] = true;
  $page['message']['message'] = l10n($message);
  $page['message']['redirect'] = $redirect;
  include($root_path.'include/message.inc.php');
}

function isAdmin($user_id)
{
  global $conf;

  return in_array($user_id, $conf['admin_users']);
}

function isTranslator($user_id)
{
  global $conf;

  return isset($conf['translator_users'][$user_id]);
}

/**
 * returns the corresponding value from $lang if existing. Else, the key is
 * returned
 *
 * @param string key
 * @return string
 */
function l10n($key)
{
  global $lang, $conf;

  if ($conf['debug_l10n'] and !isset($lang[$key]) and !empty($key))
  {
    trigger_error('[l10n] language key "'.$key.'" is not defined', E_USER_WARNING);
  }

  return isset($lang[$key]) ? $lang[$key] : $key;
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
 * stupidly returns the current microsecond since Unix epoch
 */
function micro_seconds()
{
  $t1 = explode(' ', microtime());
  $t2 = explode('.', $t1[0]);
  $t2 = $t1[1].substr($t2[1], 0, 6);
  return $t2;
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

function array_of_arrays_from_query($query)
{
  global $db;
  
  $array = array();

  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result))
  {
    array_push($array, $row);
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

    if (!isset($version_ids_of[$revision_id])) {
      $versions_of[$revision_id] = array('none');
      continue;
    }

    foreach ($version_ids_of[$revision_id] as $version_id)
    {
      array_push(
        $versions_of[$revision_id],
        $version_name_of[$version_id]
        );
    }

    $versions_of[$revision_id] = array_reverse(versort($versions_of[$revision_id]));
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
        if (isset($version_ids_of_revision[$revision_id])) {
          $version_ids_of_extension[$extension_id] = array_merge(
            $version_ids_of_extension[$extension_id],
            $version_ids_of_revision[$revision_id]
            );
        }
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
    r.description AS default_description,
    r.idx_language,
    url,
    accept_agreement,
    author,
    rt.description
  FROM '.REV_TABLE.' AS r
    LEFT JOIN '.REV_TRANS_TABLE.' AS rt
    ON r.id_revision = rt.idx_revision
    AND rt.idx_language = '.$_SESSION['language']['id'].'
  WHERE id_revision IN ('.implode(',', $revision_ids).')
;';
  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result))
  {
    if (empty($row['description']))
    {
      $row['description'] = $row['default_description'];
    }
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
       rating_score,
       idx_user,
       svn_url,
       e.description AS default_description,
       et.description
  FROM '.EXT_TABLE.' AS e
  LEFT JOIN '.EXT_TRANS_TABLE.' AS et
    ON e.id_extension = et.idx_extension
    AND et.idx_language = '.$_SESSION['language']['id'].'
  WHERE id_extension IN ('.$ids_string.')
;';
  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result))
  {
    if (empty($row['description']))
    {
      $row['description'] = $row['default_description'];
    }
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

function create_pagination_bar($base_url, $nb_pages, $current_page, $param_name)
{
  global $conf;

  $navbar = array();
  $pages_around = $conf['paginate_pages_around'];
  $url = $base_url.(preg_match('/\?/', $base_url) ? '&amp;' : '?').$param_name.'=';

  // current page detection
  if (!isset($current_page) or !is_numeric($current_page) or $current_page < 0)
  {
    $current_page = 1;
  }

  // navigation bar useful only if more than one page to display !
  if ($nb_pages > 1)
  {
    $navbar['CURRENT_PAGE'] = $current_page;

    // link to first and previous page?
    if ($current_page > 1)
    {
      $navbar['URL_FIRST'] = $url . 1;
      $navbar['URL_PREV'] = $url . ($current_page - 1);
    }
    // link on next page?
    if ($current_page < $nb_pages)
    {
      $navbar['URL_NEXT'] = $url . ($current_page + 1);
      $navbar['URL_LAST'] = $url . $nb_pages;
    }

    // pages to display
    $navbar['pages'] = array();
    $navbar['pages'][1] = $url;
    $navbar['pages'][$nb_pages] = $url.$nb_pages;

    for ($i = max($current_page - $pages_around, 2), $stop = min($current_page + $pages_around + 1, $nb_pages);
         $i < $stop; $i++)
    {
      $navbar['pages'][$i] = $url.$i;
    }
    ksort($navbar['pages']);
  }
  return $navbar;
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
    @unlink(
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
  global $conf;

  return $conf['upload_dir'].'extension-'.$extension_id;
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

  $query = '
UPDATE '.REV_TABLE.'
  SET nb_downloads = nb_downloads + 1
  WHERE id_revision = '.$revision_id.'
;';
  $db->query($query);
}

function get_download_of_extension($extension_ids) {
  global $db;

  if (count($extension_ids) == 0) {
    return array();
  }

  $downloads_of_extension = array();

  foreach ($extension_ids as $id) {
    $downloads_of_extension[$id] = 0;
  }

  $query = '
SELECT
    idx_extension AS extension_id,
    SUM(nb_downloads) AS sum_downloads
  FROM '.REV_TABLE.'
  WHERE idx_extension IN ('.implode(',', $extension_ids).')
  GROUP BY idx_extension
;';

  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result)) {
    $downloads_of_extension[ $row['extension_id'] ] = $row['sum_downloads'];
  }

  return $downloads_of_extension;
}

function get_download_of_revision($revision_ids) {
  global $db;
  
  if (count($revision_ids) == 0) {
    return array();
  }

  $downloads_of_revision = array();

  foreach ($revision_ids as $id) {
    $downloads_of_revision[$id] = 0;
  }

  $query = '
SELECT
    id_revision,
    nb_downloads
  FROM '.REV_TABLE.'
  WHERE id_revision IN ('.implode(',', $revision_ids).')
;';
  $result = $db->query($query);
  
  while ($row = $db->fetch_assoc($result)) {
    $downloads_of_revision[ $row['id_revision'] ] = $row['nb_downloads'];
  }

  return $downloads_of_revision;
}

function compare_username($a, $b) {
  return strcmp(strtolower($a["username"]), strtolower($b["username"]));
}

function get_Subversion_revision() {
  global $root_path;

  // this piece of code was copied from FluxBB extension "Show revision"
  // written by "the DtTvB"
  if (file_exists($root_path . '.svn/entries')) {
    if (preg_match_all('~^\\S.*$~m', file_get_contents($root_path . '.svn/entries'), $matches)) {
      if (!empty($matches[0][2])) {
        return 'r' . intval(trim($matches[0][2]));
      }
    }
  }

  return null;
}

// fix_magic_quotes undo what magic_quotes has done. The script was taken
// from http://www.nyphp.org/phundamentals/storingretrieving.php
//
// strings are protected only in the $db->query function
function fix_magic_quotes($var = NULL, $sybase = NULL) {
  // if sybase style quoting isn't specified, use ini setting
  if (!isset($sybase)) {
    $sybase = ini_get ('magic_quotes_sybase');
  }

  // if no var is specified, fix all affected superglobals
  if (!isset($var)) {
    // if magic quotes is enabled
    if (get_magic_quotes_gpc()) {
      // workaround because magic_quotes does not change $_SERVER['argv']
      $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : NULL; 

      // fix all affected arrays
      foreach (array('_ENV', '_REQUEST', '_GET', '_POST', '_COOKIE', '_SERVER') as $var) {
        $GLOBALS[$var] = fix_magic_quotes($GLOBALS[$var], $sybase);
      }

      $_SERVER['argv'] = $argv;

      // turn off magic quotes, this is so scripts which are sensitive to
      // the setting will work correctly
      ini_set('magic_quotes_gpc', 0);
    }

    // disable magic_quotes_sybase
    if ($sybase) {
      ini_set('magic_quotes_sybase', 0);
    }

    // disable magic_quotes_runtime
    @set_magic_quotes_runtime(0);
    return TRUE;
  }

  // if var is an array, fix each element
  if (is_array($var)) {
    foreach ($var as $key => $val) {
      $var[$key] = fix_magic_quotes($val, $sybase);
    }

    return $var;
  }

  // if var is a string, strip slashes
  if (is_string($var)) {
    return $sybase ? str_replace ('\'\'', '\'', $var) : stripslashes ($var);
  }

  // otherwise ignore
  return $var;
}

function debug($var) {
  global $conf;

  if ($conf['debug_mode']) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
  }
}

function pun_setcookie($user_id, $password_hash)
{
  global $conf;
  
  $cookie_name = $conf['user_cookie_name'];
  $cookie_domain = '';
  $cookie_path = $conf['cookie_path'];
  $cookie_secure = 0;
  $cookie_seed = $conf['cookie_seed'];
  $cookie_expire = strtotime('+1 year');

  if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
    setcookie(
      $cookie_name,
      serialize(array($user_id, md5($cookie_seed.$password_hash))),
      $cookie_expire,
      $cookie_path,
      $cookie_domain,
      $cookie_secure,
      true
      );
  }
  else {
    setcookie(
      $cookie_name,
      serialize(array($user_id, md5($cookie_seed.$password_hash))),
      $cookie_expire,
      $cookie_path.'; HttpOnly',
      $cookie_domain,
      $cookie_secure
      );
  }
}

/**
 * includes a language file
 */
function load_language($filename, $no_fallback = false, $dirname = './')
{
  global $conf, $lang;

  $dirname .= 'language/';

  $selected_language_file = $dirname . $_SESSION['language']['code'] . '/' . $filename;
  $default_language_file = $dirname . $conf['default_language'] . '/' . $filename;

  if (file_exists($selected_language_file))
  {
    @include($selected_language_file);
  }
  elseif (!$no_fallback and file_exists( $default_language_file))
  {
    @include($default_language_file);
  }
}

function get_extension_ids_for_categories($category_ids, $mode=null) {
  if (count($category_ids) == 0) {
    return array();
  }

  if (!in_array($mode, array('or', 'and'))) {
    $mode = 'and';
  }

  // strategy is to list images associated to each category
  $eids_for_category = array();

  if ($mode == 'and') {
    foreach ($category_ids as $cid) {
      $query = '
SELECT idx_extension
  FROM '.EXT_CAT_TABLE.'
  WHERE idx_category = '.$cid.'
;';
      $eids_for_category[$cid] = array_from_query($query, 'idx_extension');
    }
  
    // then we calculate the intersection, the images that are associated to
    // every tags
    $eids = array_shift($eids_for_category);
    foreach ($eids_for_category as $category_ids) {
      $eids = array_intersect($eids, $category_ids);
    }
  }
  else {
    $query = '
SELECT
    DISTINCT(idx_extension)
  FROM '.EXT_CAT_TABLE.'
  WHERE idx_category IN ('.implode(',', $category_ids).')
;';
    $eids = array_from_query($query, 'idx_extension');
  }

  return array_unique($eids);
}

function get_extension_ids_for_tags($tag_ids, $mode=null) {
  if (count($tag_ids) == 0) {
    return array();
  }

  if (!in_array($mode, array('or', 'and'))) {
    $mode = 'and';
  }

  // strategy is to list images associated to each category
  $eids_for_tag = array();

  if ($mode == 'and') {
    foreach ($tag_ids as $tid) {
      $query = '
SELECT idx_extension
  FROM '.EXT_TAG_TABLE.'
  WHERE idx_tag = '.$tid.'
;';
      $eids_for_tag[$tid] = array_from_query($query, 'idx_extension');
    }
  
    // then we calculate the intersection, the images that are associated to
    // every tags
    $eids = array_shift($eids_for_tag);
    foreach ($eids_for_tag as $tag_ids) {
      $eids = array_intersect($eids, $tag_ids);
    }
  }
  else {
    $query = '
SELECT
    DISTINCT(idx_extension)
  FROM '.EXT_TAG_TABLE.'
  WHERE idx_tag IN ('.implode(',', $tag_ids).')
;';
    $eids = array_from_query($query, 'idx_extension');
  }

  return array_unique($eids);
}

function get_categories_of_extension($extension_ids) {
  global $db;
  
  $cat_list_for = array();

  $query = '
SELECT
    id_category,
    c.name AS default_name,
    ct.name,
    idx_extension
  FROM '.EXT_CAT_TABLE.' AS ec
    JOIN '.CAT_TABLE.' AS c
      ON id_category = ec.idx_category
    LEFT JOIN '.CAT_TRANS_TABLE.' AS ct
      ON id_category = ct.idx_category
      AND ct.idx_language = '.$_SESSION['language']['id'].'
  WHERE idx_extension IN ('.implode(',', $extension_ids).')
;';

  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result)) {
    $id_extension = $row['idx_extension'];

    if (empty($row['name']))
    {
      $row['name'] = $row['default_name'];
    }
    
    if (!isset($cat_list_for[$id_extension])) {
      $cat_list_for[$id_extension] = array();
    }

    array_push(
      $cat_list_for[$id_extension],
      sprintf(
        '<a href="index.php?cid=%u">%s</a>',
        $row['id_category'],
        $row['name']
        )
      );
  }

  $categories_of_extension = array();
  foreach ($extension_ids as $extension_id) {
    $categories_of_extension[$extension_id] = implode(
      ', ',
      $cat_list_for[$extension_id]
      );
  }

  return $categories_of_extension;
}

function get_tags_of_extension($extension_ids) {
  global $db;
  
  $cat_list_for = array();
  
  $query = '
SELECT idx_extension,
       id_tag,
       name
  FROM '.TAG_TABLE.' AS t
  LEFT JOIN '.EXT_TAG_TABLE.' AS et
    ON et.idx_tag = t.id_tag
  WHERE et.idx_extension IN ('.implode(',', $extension_ids).')
;';
  $result = $db->query($query);
  
  while ($row = $db->fetch_assoc($result)) {
    $id_extension = $row['idx_extension'];
    
    if (!isset($cat_list_for[$id_extension])) {
      $cat_list_for[$id_extension] = array();
    }

    array_push(
      $cat_list_for[$id_extension],
      sprintf(
        '<a href="index.php?tid=%u">%s</a>',
        $row['id_tag'],
        $row['name']
        )
      );
  }

  $categories_of_extension = array();
  foreach ($extension_ids as $extension_id) {
    $categories_of_extension[$extension_id] = implode(
      ', ',
      isset($cat_list_for[$extension_id]) ? $cat_list_for[$extension_id] : array()
      );
  }

  return $categories_of_extension;
}

function get_extension_ids_for_version($id_version) {
  $query = '
SELECT
    DISTINCT id_extension
  FROM '.EXT_TABLE.' AS e
    JOIN '.REV_TABLE.' AS r ON r.idx_extension = e.id_extension
    JOIN '.COMP_TABLE.' AS c ON c.idx_revision = r.id_revision
  WHERE idx_version = '.$id_version.'
;';
  return array_from_query($query, 'id_extension');
}

function get_extension_ids_for_search($search) {
   // search is performed on extension name (10 points), 
   // tags (8 pts), description (6 pts), revision note (4 pts)
   // one point is removed every month of antiquity
   
  global $db;
  $search_result = array();

  // Split words
  $replace_by = array(
    '-' => ' ', '^' => ' ', '$' => ' ', ';' => ' ', '#' => ' ', '&' => ' ',
    '(' => ' ', ')' => ' ', '<' => ' ', '>' => ' ', '`' => '', '\'' => '',
    '"' => ' ', '|' => ' ', ',' => ' ', '@' => ' ', '_' => '', '?' => ' ',
    '%' => ' ', '~' => ' ', '.' => ' ', '[' => ' ', ']' => ' ', '{' => ' ',
    '}' => ' ', ':' => ' ', '\\' => '', '/' => ' ', '=' => ' ', '\'' => ' ',
    '!' => ' ', '*' => ' ',
    );
  $words = array_unique(
    preg_split(
      '/\s+/',
      str_replace(
        array_keys($replace_by),
        array_values($replace_by),
        $search
        )
      )
    );
  $add_bracked = create_function('&$s','$s="(".$s.")";');
  
  // search on extension name
  $word_clauses = array();
  foreach ($words as $word) {
    array_push($word_clauses, "e.name LIKE '%".$word."%'");
  }
  array_walk(
    $word_clauses,
    $add_bracked
    );
  $query = '
SELECT
    id_extension
  FROM '.EXT_TABLE.' AS e
  WHERE '.implode("\n    AND ", $word_clauses).'
;';
  $result = array_from_query($query, 'id_extension');
  foreach ($result as $ext_id) {
    if (!empty($search_result[$ext_id])) {
      $search_result[$ext_id]+= 10;
    } else {
      $search_result[$ext_id] = 10;
    }
  }
  
  // search on tags
  $word_clauses = array();
  foreach ($words as $word) {
    array_push($word_clauses, "LOWER(t.name) LIKE '%".strtolower($word)."%'");
  }
  array_walk(
    $word_clauses,
    $add_bracked
    );
  $query = '
SELECT
    idx_extension
  FROM '.EXT_TAG_TABLE.' AS et
    LEFT JOIN '.TAG_TABLE.' AS t
      ON et.idx_tag = t.id_tag
  WHERE '.implode("\n    OR ", $word_clauses).'
;';
  $result = array_from_query($query, 'idx_extension');
  foreach ($result as $ext_id) {
    if (!empty($search_result[$ext_id])) {
      $search_result[$ext_id]+= 8;
    } else {
      $search_result[$ext_id] = 8;
    }
  }
  
  // search on extension description
  $word_clauses = array();
  foreach ($words as $word) {
    $field_clauses = array();
    foreach (array('e.description', 'et.description') as $field) {
      array_push($field_clauses, $field." LIKE '%".$word."%'");
    }
    array_push(
      $word_clauses,
      implode("\n          OR ", $field_clauses)
      );
  }
  array_walk(
    $word_clauses,
    $add_bracked
    );
  $query = '
SELECT
    id_extension
  FROM '.EXT_TABLE.' AS e
    LEFT JOIN '.EXT_TRANS_TABLE.' AS et
      ON e.id_extension = et.idx_extension
      AND et.idx_language = '.$_SESSION['language']['id'].'
  WHERE '.implode("\n    AND ", $word_clauses).'
;';
  $result = array_from_query($query, 'id_extension');
  foreach ($result as $ext_id) {
    if (!empty($search_result[$ext_id])) {
      $search_result[$ext_id]+= 6;
    } else {
      $search_result[$ext_id] = 6;
    }
  }
  
  // search on revision description
  $word_clauses = array();
  foreach ($words as $word) {
    $field_clauses = array();
    foreach (array('r.description', 'rt.description') as $field) {
      array_push($field_clauses, $field." LIKE '%".$word."%'");
    }
    array_push(
      $word_clauses,
      implode("\n          OR ", $field_clauses)
      );
  }
  array_walk(
    $word_clauses,
    $add_bracked
    );
  $query = '
SELECT
    DISTINCT(idx_extension) AS id_extension
  FROM '.REV_TABLE.' AS r
    LEFT JOIN '.REV_TRANS_TABLE.' AS rt
      ON r.id_revision = rt.idx_revision
      AND rt.idx_language = '.$_SESSION['language']['id'].'
  WHERE '.implode("\n    AND ", $word_clauses).'
;';
  $result = array_from_query($query, 'id_extension');
  foreach ($result as $ext_id) {
    if (!empty($search_result[$ext_id])) {
      $search_result[$ext_id]+= 4;
    } else {
      $search_result[$ext_id] = 4;
    }
  }
  
  // minor rank by the date of last revision (remove 1 point for every month)
  if (count($search_result)) {
    $time = time();
    $query = '
SELECT
    idx_extension,
    MAX(date) AS date
  FROM '.REV_TABLE.'
  WHERE idx_extension IN ('.implode(',', array_keys($search_result)).')
;';
    $result = $db->query($query);
    while ($row = $db->fetch_array($result))
    {
      $search_result[ $row['idx_extension'] ]-= ($time - $row['date']) / (60*60*24*7*30);
    }
    
    arsort($search_result);
  }
  
  return array_keys($search_result);
}

function get_extension_ids_for_user($user_id) {
  $query = '
SELECT
    id_extension
 FROM '.EXT_TABLE.'
 WHERE idx_user = '.$user_id.'
UNION ALL
SELECT
    idx_extension AS id_extension
 FROM '.AUTHORS_TABLE.'
 WHERE idx_user = '.$user_id.'
;';
  return array_from_query($query, 'id_extension');
}

function get_filtered_extension_ids($filter) {
  $filtered_sets = array();

  if (isset($filter['id_version'])) {
    $filtered_sets['id_version'] = get_extension_ids_for_version($filter['id_version']);
  }

  if (isset($filter['search'])) {
    $filtered_sets['search'] = get_extension_ids_for_search($filter['search']);
  }

  if (isset($filter['category_ids'])) {
    $filtered_sets['category_ids'] = get_extension_ids_for_categories(
      $filter['category_ids'],
      $filter['category_mode']
      );
  }
  
  if (isset($filter['tag_ids'])) {
    $filtered_sets['tag_ids'] = get_extension_ids_for_tags(
      $filter['tag_ids'],
      $filter['tag_mode']
      );
  }

  if (isset($filter['id_user'])) {
    $filtered_sets['id_user'] = get_extension_ids_for_user($filter['id_user']);
  }

  $filtered_extension_ids = array_shift($filtered_sets);
  foreach ($filtered_sets as $set) {
    $filtered_extension_ids = array_intersect(
      $filtered_extension_ids,
      $set
      );
  }
  
  return array_unique($filtered_extension_ids);
}

function compare_field($a, $b) {
  global $sort_field;
  
  if ($a[$sort_field] == $b[$sort_field]) {
    return 0;
  }

  return ($a[$sort_field] < $b[$sort_field]) ? -1 : 1;
}

function sort_by_field($array, $fieldname) {
  $sort_field = $fieldname;
  usort($array, 'compare_field');
  return $array;
}

function get_extension_authors($extension_id)
{
  $authors = array();

  $query = '
SELECT idx_user
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$extension_id.'
UNION ALL
SELECT idx_user
  FROM '.AUTHORS_TABLE.'
  WHERE idx_extension = '.$extension_id.'
;';

  return array_from_query($query, 'idx_user');
}

function get_author_name($ids)
{
  global $conf;

  if (is_string($ids))
  {
    $authors = array($ids);
  }
  else
  {
    $authors = $ids;
  }

  $result = array();
  foreach($authors as $author)
  {
    $user_infos_of = get_user_infos_of(array($author));

    if (!empty($conf['user_url_template']))
    {
      $author_string = sprintf(
        $conf['user_url_template'],
        $user_infos_of[$author]['id'],
        $user_infos_of[$author]['username']
        );
    }
    else
    {
      $author_string = $user_infos_of[$author]['username'];
    }
    array_push($result, $author_string);
  }
  if (is_string($ids))
  {
    return $result[0];
  }
  return $result;
}

function get_tag_ids($raw_tags, $allow_create=true)
{
  // In $raw_tags we receive something like "~~6~~,~~59~~,New tag,Another new tag"
  // The ~~34~~ means that it is an existing tag. I've added the surrounding ~~ 
  // to permit creation of tags like "10" or "1234" (numeric characters only)
  
  if (empty($raw_tags)) return array();
  
  global $db;

  $tag_ids = array();
  $raw_tags = explode(',',$raw_tags);
  
  foreach ($raw_tags as $raw_tag)
  {
    if (preg_match('/^~~(\d+)~~$/', $raw_tag, $matches))
    {
      array_push($tag_ids, $matches[1]);
    }
    elseif ($allow_create)
    {
      // does the tag already exists?
      $query = '
SELECT id_tag
  FROM '.TAG_TABLE.'
  WHERE name = "'.$raw_tag.'"
;';
      $existing_tags = array_from_query($query, 'id_tag');

      if (count($existing_tags) == 0)
      {
        mass_inserts(
          TAG_TABLE,
          array('name'),
          array(
            array(
              'name' => $raw_tag,
              )
            )
          );
        array_push($tag_ids, $db->insert_id());
      }
      else
      {
        array_push($tag_ids, $existing_tags[0]);
      }
    }
  }

  return $tag_ids;
}

function get_tag_name_from_id($id)
{
  global $db;
  
  $query = '
SELECT
    name
  FROM '.TAG_TABLE.'
  WHERE id_tag = '.$id.'
;';
  $result = $db->query($query);
  
  list($name) = $db->fetch_row($result);
  return $name;
}

function deltree($path)
{
  if (is_dir($path))
  {
    $fh = opendir($path);
    while ($file = readdir($fh))
    {
      if ($file != '.' and $file != '..')
      {
        $pathfile = $path . '/' . $file;
        if (is_dir($pathfile))
        {
          deltree($pathfile);
        }
        else
        {
          @unlink($pathfile);
        }
      }
    }
    closedir($fh);
    return @rmdir($path);
  }
}

function get_languages_of_revision($revision_ids)
{
  global $db;

  $languages_of = array();
  $languages_ids_of = get_language_ids_of_revision($revision_ids);

  $query = 'SELECT id_language, code, name FROM '.LANG_TABLE.';';
  $result = $db->query($query);
  $languages_data = array();
  while ($row = mysql_fetch_assoc($result))
  {
    $languages_data[ $row['id_language'] ] = $row;
  }

  foreach ($revision_ids as $revision_id)
  {
    if (!empty($languages_ids_of[$revision_id]))
    {
      $languages_of[$revision_id] = array();

      foreach ($languages_ids_of[$revision_id] as $language_id)
      {
        array_push(
          $languages_of[$revision_id],
          $languages_data[$language_id]
          );
      }
    }
  }

  return $languages_of;
}

function get_language_ids_of_revision($revision_ids)
{
  global $db;

  if (count($revision_ids) == 0)
  {
    return array();
  }
  
  $languages_of = array();
  
  $query = '
SELECT rv.idx_revision,
       l.id_language
  FROM '.REV_LANG_TABLE.' AS rv
  INNER JOIN '.LANG_TABLE.' AS l
    ON rv.idx_language = l.id_language
  WHERE idx_revision IN ('.implode(',', $revision_ids).')
  ORDER BY l.name
;';
  
  $result = $db->query($query);
  
  while ($row = $db->fetch_array($result))
  {
    $languages_of[ $row['idx_revision'] ][] = $row['id_language'];
  }

  return $languages_of;
}

function get_current_language()
{
  global $db, $conf;
  
  $language = null;
  
  $interface_languages = get_interface_languages();
  
  if (isset($_GET['lang']))
  {
    $language = @$interface_languages[$_GET['lang']];
  }
  else if (isset($_SESSION['language']))
  {
    $language = $_SESSION['language'];
  }
  
  if (empty($language) or !is_array($language))
  {
    $language = $interface_languages[$conf['default_language']];
    
    if ($conf['get_browser_language'])
    {
      $browser_language = @substr($_SERVER["HTTP_ACCEPT_LANGUAGE"],0,2);
      foreach ($interface_languages as $interface_language)
      {
        if (substr($interface_language['code'], 0, 2) == $browser_language)
        {
          $language = $interface_languages[$interface_language['code']];
          break;
        }
      }
    }
  }

  return $language;
}

function get_current_language_id()
{
  $language = null;
  
  if (isset($_SESSION['language']))
  {
    $language = $_SESSION['language'];
  }
  else
  {
    $language = get_current_language();
  }
  
  return $language['id'];
}

function get_interface_languages()
{
  global $db, $conf, $cache;

  if (isset($cache['interface_languages']))
  {
    return $cache['interface_languages'];
  }
  
  $query = '
SELECT id_language AS id,
       code,
       name
  FROM '.LANG_TABLE.'
  WHERE interface = "true"
  ORDER BY name
;';
  $result = $db->query($query);
  $interface_languages = array();
  while ($row = mysql_fetch_assoc($result))
  {
    $interface_languages[$row['code']] = $row;
  }

  $cache['interface_languages'] = $interface_languages;

  return $cache['interface_languages'];
}

function rate_extension($extension_id, $rate)
{
  global $db, $user;

  if ( !isset($rate) or !isset($extension_id) )
  {
    return false;
  }

  // get user infos
  $user_anonymous = empty($user['id']);
  $user_id = $user_anonymous ? 0 : $user['id'];

  $ip_components = explode('.', $_SERVER["REMOTE_ADDR"]);
  if (count($ip_components) > 3)
  {
    array_pop($ip_components);
  }
  $anonymous_id = implode ('.', $ip_components);

  if ($user_anonymous)
  {
    $save_anonymous_id = !empty($_SESSION['anonymous_rater']) ? $_SESSION['anonymous_rater'] : $anonymous_id;

    if ($anonymous_id != $save_anonymous_id)
    { // client has changed his IP adress or he's trying to fool us
      $query = '
SELECT idx_extension
  FROM '.RATE_TABLE.'
  WHERE
    idx_user = '.$user_id.'
    AND anonymous_id = "'.$anonymous_id.'"
;';
      $already_there = array_from_query($query, 'idx_extension');

      if (count($already_there) > 0)
      {
        $query = '
DELETE
  FROM '.RATE_TABLE.'
  WHERE 
    idx_user = '.$user_id.'
    AND anonymous_id = "'.$save_anonymous_id.'"
    AND idx_extension IN ('.implode(',', $already_there).')
;';
         $db->query($query);
       }

       $query = '
UPDATE '.RATE_TABLE.'
  SET anonymous_id = "'.$anonymous_id.'"
  WHERE 
    idx_user = '.$user_id.'
    AND anonymous_id = "'.$save_anonymous_id.'"
;';
       $db->query($query);
    } // end client changed ip

    $_SESSION['anonymous_rater'] = $anonymous_id;
  } // end anonymous user

  // insert/update rate
  $query = '
DELETE
  FROM '.RATE_TABLE.'
  WHERE 
    idx_extension = '.$extension_id.'
    AND idx_user = '.$user_id.'
';
  if ($user_anonymous)
  {
    $query.= '
    AND anonymous_id = "'.$anonymous_id.'"';
  }
  $query.= '
;';
  $db->query($query);
  
  if ($rate != 'null')
  {
    $query = '
INSERT
  INTO '.RATE_TABLE.' (
    idx_user,
    idx_extension,
    anonymous_id,
    rate,
    date
  )
  VALUES (
    '.$user_id.',
    '.$extension_id.',
    "'.$anonymous_id.'",
    '.$rate.',
    NOW()
  )
;';
    $db->query($query);
  }
  
  // update extension rating score
  $query = '
SELECT rate
  FROM '.RATE_TABLE.'
  WHERE idx_extension = '.$extension_id.'
;';
  $rates = array_from_query($query, 'rate');
  
  $query = '
UPDATE '.EXT_TABLE.'
  SET rating_score = '.(count($rates)>0 ? array_sum($rates)/count($rates) : 'NULL').'
  WHERE id_extension = '.$extension_id.'
;';
  $db->query($query);
}

?>
