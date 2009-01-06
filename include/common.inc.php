<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2009 PEM Team - http://home.gna.org/pem            |
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

define('JTPL_TEMPLATES_PATH', $root_path.'template/');

// determine the initial instant to indicate the generation time of this page
$page['start'] = intval(microtime(true) * 1000);

// header('Content-Type: text/html; charset=utf-8');

// Hacking attempt
if(!defined('INTERNAL'))
{
  die('No right to do that, sorry. :)');
}

session_name('pem_session_id');
session_start();
    
require_once($root_path . 'include/config_default.inc.php');
@include($root_path . 'include/config_local.inc.php');
require_once($root_path . 'include/constants.inc.php');
require_once($root_path . 'include/functions.inc.php');
require_once($root_path . 'include/functions_user.inc.php');
require_once($root_path . 'include/dblayer/common_db.php');
require_once($root_path . 'include/jtpl/jtpl_standalone_prepend.php');

// secure user incoming data
//
// First we undo what has been done magically
fix_magic_quotes();

// Then we "sanitize" data out own way
$_GET = $db->escape_array($_GET);
$_POST = $db->escape_array($_POST);
$_COOKIE = $db->escape_array($_COOKIE);

// user informations
$user = array();

if (isset($_SESSION['user_id']))
{
  $user_infos_of = get_user_infos_of(array($_SESSION['user_id']));
  $user = $user_infos_of[ $_SESSION['user_id'] ];
}

// echo '<pre>cookie: '; print_r($_COOKIE); echo '</pre>';
// echo '<pre>session: '; print_r($_SESSION); echo '</pre>';
// echo '<pre>user: '; print_r($user); echo '</pre>';

$tpl = new jTPL();
$tpl->assign('software', $conf['software']);

// do we have a disclaimer?
$has_disclaimer = false;
if (is_file($root_path.'template/disclaimer.html'))
{
  $has_disclaimer = true;
}

$tpl->assign('has_disclaimer', $has_disclaimer);

// PWG Compatibility version set
if (isset($_POST['filter_submit'])) {
  // filter on the extended application version
  if (isset($_POST['pwg_version']) and is_numeric($_POST['pwg_version'])) {
    // If the field is empty, this means that the user wants to cancel the
    // compatibility version setting
    if (!empty($_POST['pwg_version'])) {
      $_SESSION['filter']['id_version'] = intval($_POST['pwg_version']);
    }
    else {
      unset($_SESSION['filter']['id_version']);
    }
  }

  // filter on a textual free search
  if (isset($_POST['search']) and !empty($_POST['search'])) {
    $_SESSION['filter']['search'] = $_POST['search'];
  }
  else {
    unset($_SESSION['filter']['search']);
  }

  // filter on a category
  if (isset($_POST['category']) and is_numeric($_POST['category'])) {
    if ($_POST['category'] != 0) {
      $_SESSION['filter']['category'] = $_POST['category'];
    }
    else {
      unset($_SESSION['filter']['category']);
    }
  }
  
  // filter on a user
  if (isset($_POST['user']) and is_numeric($_POST['user'])) {
    if ($_POST['user'] != 0) {
      $_SESSION['filter']['user'] = $_POST['user'];
    }
    else {
      unset($_SESSION['filter']['user']);
    }
  }
}

if (isset($_POST['filter_reset'])) {
  if (isset($_SESSION['filter'])) {
    unset($_SESSION['filter']);
  }
}

if (isset($_POST['filter_reset']) or isset($_POST['filter_submit'])) {
  unset($_GET['page']);
}

// if a filter is active, we must prepare a filtered list of extensions
if (isset($_SESSION['filter']) and count($_SESSION['filter']) > 0) {
  $filtered_sets = array();
  
  if (isset($_SESSION['filter']['id_version'])) {
    $query = '
SELECT
    DISTINCT id_extension
  FROM '.EXT_TABLE.' AS e
    JOIN '.REV_TABLE.' AS r ON r.idx_extension = e.id_extension
    JOIN '.COMP_TABLE.' AS c ON c.idx_revision = r.id_revision
  WHERE idx_version = '.$_SESSION['filter']['id_version'].'
;';
    $filtered_sets['version'] = array_from_query($query, 'id_extension');
  }

  if (isset($_SESSION['filter']['search'])) {
    $fields = array('e.name', 'e.description', 'r.description');

    $replace_by = array(
      '-' => ' ',
      '^' => ' ',
      '$' => ' ',
      ';' => ' ',
      '#' => ' ',
      '&' => ' ',
      '(' => ' ',
      ')' => ' ',
      '<' => ' ',
      '>' => ' ',
      '`' => '',
      '\'' => '',
      '"' => ' ',
      '|' => ' ',
      ',' => ' ',
      '@' => ' ',
      '_' => '',
      '?' => ' ',
      '%' => ' ',
      '~' => ' ',
      '.' => ' ',
      '[' => ' ',
      ']' => ' ',
      '{' => ' ',
      '}' => ' ',
      ':' => ' ',
      '\\' => '',
      '/' => ' ',
      '=' => ' ',
      '\'' => ' ',
      '!' => ' ',
      '*' => ' ',
      );
    
    // Split words
    $words = array_unique(
      preg_split(
        '/\s+/',
        str_replace(
          array_keys($replace_by),
          array_values($replace_by),
          $_SESSION['filter']['search']
          )
        )
      );

    // ((field1 LIKE '%word1%' OR field2 LIKE '%word1%')
    // AND (field1 LIKE '%word2%' OR field2 LIKE '%word2%'))
    $word_clauses = array();
    foreach ($words as $word) {
      $field_clauses = array();
      foreach ($fields as $field) {
        array_push($field_clauses, $field." LIKE '%".$word."%'");
      }
      // adds brackets around where clauses
      array_push(
        $word_clauses,
        implode(
          "\n          OR ",
          $field_clauses
          )
        );
    }

    array_walk(
      $word_clauses,
      create_function('&$s','$s="(".$s.")";')
      );

    $clause = implode(
      "\n         AND\n         ",
      $word_clauses
      );

    $query = '
SELECT
    id_extension
  FROM '.EXT_TABLE.' AS e
    JOIN '.REV_TABLE.' AS r ON r.idx_extension = e.id_extension
  WHERE '.$clause.'
;';
    $filtered_sets['search'] = array_from_query($query, 'id_extension');
  }

  if (isset($_SESSION['filter']['category'])) {
    $query = '
SELECT
    idx_extension
 FROM '.EXT_CAT_TABLE.'
 WHERE idx_category = '.$_SESSION['filter']['category'].'
;';
    $filtered_sets['category'] = array_from_query($query, 'idx_extension');
  }

  if (isset($_SESSION['filter']['user'])) {
    $query = '
SELECT
    id_extension
 FROM '.EXT_TABLE.'
 WHERE idx_user = '.$_SESSION['filter']['user'].'
;';
    $filtered_sets['user'] = array_from_query($query, 'id_extension');
  }

  $page['filtered_extension_ids'] = array();
  $is_first_set = true;
  foreach ($filtered_sets as $set) {
    if ($is_first_set) {
      $is_first_set = false;

      $page['filtered_extension_ids'] = $set;
    }
    else {
      $page['filtered_extension_ids'] = array_intersect(
        $page['filtered_extension_ids'],
        $set
        );
    }
  }
  
  $page['filtered_extension_ids'] = array_unique(
    $page['filtered_extension_ids']
    );

  $page['filtered_extension_ids_string'] = implode(
    ',',
    $page['filtered_extension_ids']
    );
}
if (!isset($user['id']))
{
if (isset($_POST['quickconnect_submit']))
{
  if ($user_id = check_user_password($_POST['username'], $_POST['password']))
  {
    log_user($user_id);

    $page['message']['is_success'] = true;
    $page['message']['message'] = l10n('Identification successful');
    $page['message']['redirect'] = 'my.php';
    include($root_path.'include/message.inc.php');
  }
  else
  {
    $page['message']['is_success'] = false;
    $page['message']['message'] = l10n('Incorrect username/password');
    $page['message']['go_back'] = true;
    include($root_path.'include/message.inc.php');
  }
}

if (isset($_GET['action']))
{
  switch ($_GET['action'])
  {
    case 'logout' :
    {
      $_SESSION = array();
      session_unset();
      session_destroy();
      setcookie(
        session_name(),
        '',
        0,
        ini_get('session.cookie_path'),
        ini_get('session.cookie_domain')
        );

      // redirect to index
      $page['message']['is_success'] = true;
      $page['message']['message'] = l10n('Logout successful');
      $page['message']['redirect'] = 'index.php';
      include($root_path.'include/message.inc.php');

      break;
    }
  }
}
}
?>
