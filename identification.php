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

define('INTERNAL', true);
$root_path = './';
require_once($root_path.'include/common.inc.php');

$tpl->set_filenames(
  array(
    'page' => 'page.tpl',
    'identification' => 'identification.tpl'
  )
);

if (isset($_POST['submit']))
{
  if ($user_id = check_user_password($_POST['username'], $_POST['password']))
  {
    log_user($user_id, $_POST['username'], $_POST['password']);

    message_success('Identification successful', 'my.php');
  }
  else
  {
    message_die('Incorrect username/password');
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

      unset($_COOKIE[ $conf['user_cookie_name'] ]);
      setcookie($conf['user_cookie_name'], false, 0, $conf['cookie_path']);

      // redirect to index
      message_success('Logout successful', 'index.php');
      break;
    }
  }
}

$tpl->assign('u_register', 'register.php');

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'identification');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>
