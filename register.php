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
    'register' => 'register.tpl'
  )
);

if (isset($_POST['submit']))
{
  $errors = array();
  
  if ($_POST['password'] != $_POST['confirmation_password'])
  {
    array_push(
      $errors,
      l10n('Confirmation password does not match.')
      );
  }

  $register_errors = register_user(
    $_POST['username'],
    $_POST['password'],
    $_POST['email']
    );

  $errors = array_merge($errors, $register_errors);

  if (count($errors) == 0)
  {
    $user_id = get_userid($_POST['username']);
    log_user($user_id, $_POST['username'], $_POST['password']);
    message_success('Registration successful', 'index.php');
  }
  else
  {
    message_die($errors[0]);
  }
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'register');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>
