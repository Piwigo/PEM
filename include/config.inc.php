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

$conf['admin_users'] = array(
  1,    // pierrick
  2,    // z0rglub
  1442, // chrisaga
  865,  // Sephi
  974,  // volcom
  1227, // VDigital
  );

// +-----------------------------------------------------------------------+
// | Database connection parameters                                        |
// +-----------------------------------------------------------------------+

$conf['db_params'] = array(
  'name' => 'pem',
  'host' => 'localhost',
  'user' => 'root',
  'pass' => 'X8lqd3GE',
  'type' => 'mysql',
  'tables_prefix' => 'pwg_',
  'persistent_connection' => true,
  );

// user_manager: 'local'
$conf['user_manager'] = 'local';

// users_table: table listing all users
$conf['users_table'] = $conf['db_params']['tables_prefix'].'users';

// user_fields : mapping between generic field names and table specific
// field names. For example, in PWG, the mail address is names
// "mail_address" and in punbb, it's called "email".
$conf['user_fields'] = array(
  'id' => 'id',
  'username' => 'username',
  'password' => 'password',
  'email' => 'email',
  );

// pass_convert : function to crypt or hash the clear user password to store
// it in the database
$conf['pass_convert'] = create_function('$s', 'return md5($s);');

// session_length: in seconds
$conf['session_length'] = 60 * 60 * 24 * 30; // 1 month by default

// title: displayed on every page
$conf['page_title'] = 'Extensions Manager';

// default_language
$conf['default_language'] = 'english';

// nb_last_revs: how many revisions show on the index?
$conf['nb_last_revs'] = 10;

// extensions_per_page: how many extensions per page?
$conf['extensions_per_page'] = 5;

// paginate_pages_around: on paginate navigation bar, how many pages display
// before and after the current page ?
$conf['paginate_pages_around'] = 2;
?>
