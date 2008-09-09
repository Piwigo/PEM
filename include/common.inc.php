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

define('JTPL_TEMPLATES_PATH', $root_path.'template/');

// determine the initial instant to indicate the generation time of this page
$page['start'] = intval(microtime(true) * 1000);

set_magic_quotes_runtime(0); // Disable magic_quotes_runtime

// echo '<pre>'; print_r($_POST); echo '</pre>';

//
// addslashes to vars if magic_quotes_gpc is off this is a security
// precaution to prevent someone trying to break out of a SQL statement.
//
if (!get_magic_quotes_gpc())
{
  if (is_array($_POST))
  {
    while (list($k, $v) = each($_POST))
    {
      if (is_array($_POST[$k]))
      {
        while (list($k2, $v2) = each($_POST[$k]))
        {
          $_POST[$k][$k2] = addslashes($v2);
        }
        @reset($_POST[$k]);
      }
      else
      {
        $_POST[$k] = addslashes($v);
      }
    }
    @reset($_POST);
  }
}

// echo '<pre>'; print_r($_POST); echo '</pre>';

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
require_once($root_path . 'include/templates.inc.php');
require_once($root_path . 'include/functions.inc.php');
require_once($root_path . 'include/functions_user.inc.php');
require_once($root_path . 'include/dblayer/common_db.php');
require_once($root_path . 'include/jtpl/jtpl_standalone_prepend.php');

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

$template = new Template($root_path . 'template');
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
if (isset($_POST['filter_submit']))
{
  // Check if the field is valid
  if (isset($_POST['pwg_version']) and is_numeric($_POST['pwg_version']))
  {
    // If the field is empty, this means that the user wants to cancel the
    // compatibility version setting
    if (!empty($_POST['pwg_version']))
    {
      $_SESSION['filter']['id_version'] = intval($_POST['pwg_version']);
    }
    else
    {
      unset($_SESSION['filter']['id_version']);
    }
  }

  if (isset($_POST['search']) and !empty($_POST['search'])) {
    $_SESSION['filter']['search'] = $_POST['search'];
  }
  else {
    unset($_SESSION['filter']['search']);
  }
}

if (isset($_POST['filter_reset'])) {
  if (isset($_SESSION['filter'])) {
    unset($_SESSION['filter']);
  }
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
    $query = '
SELECT
    id_extension
  FROM '.EXT_TABLE.' AS e
    JOIN '.REV_TABLE.' AS r ON r.idx_extension = e.id_extension
  WHERE MATCH (
    e.name,
    e.description,
    r.description
  ) AGAINST (\''.$_SESSION['filter']['search'].'\' IN BOOLEAN MODE)
;';
    $filtered_sets['search'] = array_from_query($query, 'id_extension');
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
?>
