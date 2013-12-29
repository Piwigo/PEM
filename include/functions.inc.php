<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2013 PEM Team - http://piwigo.org                  |
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

include_once($root_path . 'include/functions_core.inc.php');
include_once($root_path . 'include/functions_database.inc.php');
include_once($root_path . 'include/functions_language.inc.php');
include_once($root_path . 'include/functions_user.inc.php');

/* this file contains misc functions */

/**
 * get absolute home url of PEM installation
 */
function get_absolute_home_url($with_scheme=true)
{
  // TODO - add HERE the possibility to call PWG functions from external scripts
  $url = '';
  if ($with_scheme)
  {
    if (isset($_SERVER['HTTPS']) &&
	((strtolower($_SERVER['HTTPS']) == 'on') or ($_SERVER['HTTPS'] == 1)))
    {
      $url .= 'https://';
    }
    else
    {
      $url .= 'http://';
    }
    $url .= $_SERVER['HTTP_HOST'];
    if ($_SERVER['SERVER_PORT'] != 80)
    {
      $url_port = ':'.$_SERVER['SERVER_PORT'];
      if (strrchr($url, ':') != $url_port)
      {
        $url .= $url_port;
      }
    }
  }
  $url .= cookie_path();
  return $url;
}

/**
 * cookie_path returns the path to use for the PEM cookie. (from Piwigo)
 * If PEM is installed on :
 * http://domain.org/meeting/gallery/category.php
 * cookie_path will return : "/meeting/gallery"
 */
function cookie_path()
{
  global $root_path;
  
  if ( isset($_SERVER['REDIRECT_SCRIPT_NAME']) and
       !empty($_SERVER['REDIRECT_SCRIPT_NAME']) )
  {
    $scr = $_SERVER['REDIRECT_SCRIPT_NAME'];
  }
  else if ( isset($_SERVER['REDIRECT_URL']) )
  {
    // mod_rewrite is activated for upper level directories. we must set the
    // cookie to the path shown in the browser otherwise it will be discarded.
    if
      (
        isset($_SERVER['PATH_INFO']) and !empty($_SERVER['PATH_INFO']) and
        ($_SERVER['REDIRECT_URL'] !== $_SERVER['PATH_INFO']) and
        (substr($_SERVER['REDIRECT_URL'],-strlen($_SERVER['PATH_INFO']))
            == $_SERVER['PATH_INFO'])
      )
    {
      $scr = substr($_SERVER['REDIRECT_URL'], 0,
        strlen($_SERVER['REDIRECT_URL'])-strlen($_SERVER['PATH_INFO']));
    }
    else
    {
      $scr = $_SERVER['REDIRECT_URL'];
    }
  }
  else
  {
    $scr = $_SERVER['SCRIPT_NAME'];
  }

  $scr = substr($scr,0,strrpos( $scr,'/'));

  // add a trailing '/' if needed
  if ((strlen($scr) == 0) or ($scr{strlen($scr)-1} !== '/'))
  {
    $scr .= '/';
  }

  if ( substr($root_path,0,3)=='../')
  {
    $scr = $scr.$root_path;
    while (1)
    {
      $new = preg_replace('#[^/]+/\.\.(/|$)#', '', $scr);
      if ($new==$scr)
      {
        break;
      }
      $scr=$new;
    }
  }
  return $scr;
}


/**
 * sort an array by version number
 */
function versort($array)
{
  if (empty($array)) return array();
  
  if (is_array($array[0])) {
    usort($array, 'pem_version_compare');
  }
  else {
    usort($array, 'version_compare');
  }

  return $array;
}

/**
 * specific version_compare for PEM
 */
function pem_version_compare($a, $b)
{
  return version_compare($a['version'], $b['version']);
}

/**
 * simple comparison between two numeric fields
 */
function compare_field($a, $b) {
  global $sort_field;
  
  if ($a[$sort_field] == $b[$sort_field]) {
    return 0;
  }

  return ($a[$sort_field] < $b[$sort_field]) ? -1 : 1;
}

/**
 * sorting function using the above comparison function
 */
function sort_by_field($array, $fieldname) {
  $sort_field = $fieldname;
  usort($array, 'compare_field');
  return $array;
}

/**
 * displays an error message
 */
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

/**
 * displays a success message
 */
function message_success($message, $redirect, $title = 'Success', $time_redirect = '5')
{
  global $root_path, $tpl, $db, $user, $page, $conf;
  
  $page['message']['title'] = l10n($title);
  $page['message']['is_success'] = true;
  $page['message']['message'] = l10n($message);
  $page['message']['redirect'] = $redirect;
  include($root_path.'include/message.inc.php');
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
 * The function get_elapsed_time returns the number of seconds (with 3
 * decimals precision) between the start time and the end time given.
 */
function get_elapsed_time($start, $end)
{
  return number_format($end - $start, 3, '.', ' ').' s';
}

/**
 * The function get_moment returns a float value corresponding to the number
 * of seconds since the unix epoch (1st January 1970) and the microseconds
 * are precised : e.g. 1052343429.89276600
 */
function get_moment()
{
  $t1 = explode( ' ', microtime() );
  $t2 = explode( '.', $t1[0] );
  $t2 = $t1[1].'.'.$t2[1];
  return $t2;
}

/**
 * extracts a subfield from nested array
 */
function array_from_subfield($hash, $field)
{
  $array = array();
  
  foreach ($hash as $row)
  {
    array_push($array, $row[$field]);
  }

  return $array;
}

/**
 * create necessary vars for the navigation bar
 */
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

/**
 * Returns the number of pages to display in a pagination bar, given the number
 * of items and the number of items per page.
 */
function get_nb_pages($nb_items, $nb_items_per_page)
{
  return intval(($nb_items - 1) / $nb_items_per_page) + 1;
}

/**
 * simply print a variable with <pre> tags
 */
function print_array($array)
{
  echo '<pre>';
  print_r($array);
  echo '</pre>';
}

/**
 * do the same only if debug mode is activated
 */
function debug($var) {
  global $conf;

  if ($conf['debug_mode']) {
    echo '<pre>';
    print_r($var);
    echo '</pre>';
  }
}

/**
 * get_boolean transforms a string to a boolean value. If the string is
 * "false" (case insensitive), then the boolean value false is returned. In
 * any other case, true is returned.
 */
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

/**
 * fix_magic_quotes undo what magic_quotes has done. The script was taken
 * from http://www.nyphp.org/phundamentals/storingretrieving.php
 * strings are protected only in the $db->query function
 */
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

/**
 * set a secured cookie
 */
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
 * recursively delete a directory
 */
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

/**
 * generate read-only rating stars as displayed by jQuery Raty
 */
function generate_static_stars($score, $space=true)
{
  if ($score === null) return null;
  
  $score = min(max($score, 0), 5);
  $floor = floor($score);
  $space = $space ? "\n" : null;
  
  $html = null;
  for ($i=1; $i<=$floor; $i++)
  {
    $html.= '<img alt="'.$i.'" src="template/jquery.raty/star-on.png">'.$space;
  }
  
  if ($score != 5)
  {
    if ($score-$floor <= .25)
    {
      $html.= '<img alt="'.($floor+1).'" src="template/jquery.raty/star-off.png">'.$space;
    }
    else if ($score-$floor <= .75)
    {
      $html.= '<img alt="'.($floor+1).'" src="template/jquery.raty/star-half.png">'.$space;
    }
    else
    {
      $html.= '<img alt="'.($floor+1).'" src="template/jquery.raty/star-on.png">'.$space;
    }
  
    for ($i=$floor+2; $i<=5; $i++)
    {
      $html.= '<img alt="'.$i.'" src="template/jquery.raty/star-off.png">'.$space;
    }
  }
  
  return $html;
}

/**
 * sends an email with PHP mail() function
 * @param:
 *   - to (list separated by comma).
 *   - subject
 *   - content
 *   - args: function params of mail function:
 *       o from [default 'noreply@domain']
 *       o cc [default empty]
 *       o bcc [default empty]
 *       o content_format [default value 'text/plain']
 * @return boolean
 */
function send_mail($to, $subject, $content, $args = array())
{
  // check inputs
  if (empty($to) and empty($args['cc']) and empty($args['bcc']))
  {
    return false;
  }

  if (empty($subject) or empty($content))
  {
    return false;
  }

  if (empty($args['from']))
  {
    $args['from'] = 'PEM <noreply@'.$_SERVER['HTTP_HOST'].'>';
  }

  if (empty($args['content_format']))
  {
    $args['content_format'] = 'text/plain';
  }

  // format subject
  $subject = trim(preg_replace('#[\n\r]+#s', null, $subject));

  // headers
  $headers = 'From: '.$args['from']."\n";

  if (!empty($args['cc']))
  {
    $headers.= 'Cc: '.implode(',', $args['cc'])."\n";
  }

  if (!empty($args['bcc']))
  {
    $headers.= 'Bcc: '.implode(',', $args['bcc'])."\n";
  }

  $headers.= 'Content-Type: '.$args['content_format'].'; charset="utf-8"'."\n";
  $headers.= 'X-Mailer: PEM'."\n";

  // content
  if ($args['content_format'] == 'text/plain')
  {
    $content = nl2br(htmlspecialchars($content, ENT_QUOTES));
  }

  // send mail
  return @mail($to, $subject, $content, $headers);
}

/**
 * returns the SVN revision of current PEM installation
 */
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

/**
 * Sends to the template all messages stored in $page and in the session.
 */
function flush_page_messages()
{
  global $tpl, $page;

  foreach (array('errors','infos','warnings') as $mode)
  {
    if (isset($_SESSION['page_'.$mode]))
    {
      $page[$mode] = array_merge($page[$mode], $_SESSION['page_'.$mode]);
      unset($_SESSION['page_'.$mode]);
    }

    if (count($page[$mode]) != 0)
    {
      $tpl->assign($mode, $page[$mode]);
    }
  }
}

?>