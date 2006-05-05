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

// determine the initial instant to indicate the generation time of this page
$t1 = explode( ' ', microtime() );
$t2 = explode( '.', $t1[0] );
$t2 = $t1[1].'.'.$t2[1];

header('Content-Type: text/html; charset=iso-8859-1');
// Hacking attempt
if(!defined('INTERNAL'))
{
  die('No right to do that, sorry. :)');
}

session_name('pem_session_id');
session_start();
    
require_once($root_path . 'include/config.inc.php');
require_once($root_path . 'include/constants.inc.php');
require_once($root_path . 'include/templates.inc.php');
require_once($root_path . 'include/functions.inc.php');
require_once($root_path . 'include/functions_user.inc.php');
require_once($root_path . 'include/dblayer/common_db.php');

// user informations
$user = array();

// if (isset($_COOKIE[session_name()]))
// {
//   session_start();
//   if (isset($_SESSION['user_id']))
//   {
//     $user = get_user_infos($_SESSION['user_id']);
//   }
// }

$user = get_user_infos(
  isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null
  );

// echo '<pre>cookie: '; print_r($_COOKIE); echo '</pre>';
// echo '<pre>session: '; print_r($_SESSION); echo '</pre>';
// echo '<pre>user: '; print_r($user); echo '</pre>';

$template = new Template($root_path . 'template');
  
// PWG Compatibility version set
if (isset($_POST['compatibility_change']))
{
  // Check if the field is valid
  if (isset($_POST['pwg_version']) and is_numeric($_POST['pwg_version']))
  {
    // If the field is empty, this means that the user wants to cancel the
    // compatibility version setting
    if (!empty($_POST['pwg_version']))
    {
      $_SESSION['id_version'] = intval($_POST['pwg_version']);
    }
    else
    {
      unset($_SESSION['id_version']);
    }
  }
}
?>
