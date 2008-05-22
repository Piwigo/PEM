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

// admin_users: give the list of user ids that can reach the administrative
// section
$conf['admin_users'] = array(
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
  'id' => 'id_user',
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

// specific_header_filepath: additionnal lines in <head> HTML tag. It can be
// a PHP file producing HTML code.
$conf['specific_header_filepath'] = 'template/specific_header.html';

// banner_filepath: where to find the banner file. It can be a PHP file
// producing HTML code.
$conf['banner_filepath'] = 'template/banner.html';

// footer_filepath: where to find the footer file. Works as the
// banner_filepath but is displayed on the page bottom.
$conf['footer_filepath'] = 'template/footer.html';

// default_language
$conf['default_language'] = 'english';

// extensions_per_page: how many extensions per page?
$conf['extensions_per_page'] = 5;

// paginate_pages_around: on paginate navigation bar, how many pages display
// before and after the current page ?
$conf['paginate_pages_around'] = 2;

// die_on_sql_error: should the application break when a SQL error happens?
$conf['die_on_sql_error'] = true;

// screenshot and associated thumbnails parameters
$conf['screenshot_maxwidth']  = 800;
$conf['screenshot_maxheight'] = 800;
$conf['thumbnail_maxheight']  = 150;
$conf['thumbnail_maxwidth']   = 150;

$conf['website_url'] = 'http://phpwebgallery.net/ext';
$conf['website_description'] = 'PhpWebGallery extensions';
$conf['website_language'] = 'en';
$conf['webmaster_email'] = 'team phpwebgallery.net';

// l10n_key_prefix: if the language key has no value, the key is
// displayed with this prefix.
$conf['l10n_key_prefix'] = '{l10n} ';

// software: name of the extended software
$conf['software'] = 'Piwigo';

// rss_nb_items: number of items to display in the RSS feed
$conf['rss_nb_items'] = 10;

// use_agreement: if an agreement is asked (mandatory or not) during
// revision add
$conf['use_agreement'] = false;

$conf['agreement_description'] = 'I accept this terms and conditions agreement that take my intellectual property on my contribution.';
?>