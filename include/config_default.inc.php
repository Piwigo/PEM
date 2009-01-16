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

// software: name of the extended software
$conf['software'] = 'Piwigo';

// rss_nb_items: number of items to display in the RSS feed
$conf['rss_nb_items'] = 10;

// use_agreement: if an agreement is asked (mandatory or not) during
// revision add
$conf['use_agreement'] = false;

$conf['debug_mode'] = false;

// cookie management
$conf['user_cookie_name'] = 'pem_auth_cookie';
$conf['cookie_path'] = '/';
$conf['cookie_seed'] = 'very secret seed';
$conf['set_cookie'] = 'pun_setcookie';

// Message time redirection (in seconds)
$conf['time_redirect'] = 5;

// languages
$conf['languages'] = array(
  'en' => 'English',
  'fr' => 'Français',
);

// Default language
$conf['default_language'] = 'en';

// Try to get browser language if true.
// If false, use $conf['default_language']
$conf['get_browser_language'] = true;

// +-----------------------------------------------------------------------+
// |                            debug/performance                          |
// +-----------------------------------------------------------------------+

// debug_l10n : display a warning message each time an unset language key is accessed
$conf['debug_l10n'] = false;

// activate template debugging - a new window will appear
$conf['debug_template'] = false;

// This tells Smarty whether to check for recompiling or not. Recompiling
// does not need to happen unless a template is changed. false results in
// better performance.
$conf['template_compile_check'] = true;

// if true, some language strings are replaced during template compilation
// (insted of template output). this results in better performance. however
// any change in the language file will not be propagated until you purge
// the compiled templates from the admin / maintenance menu
$conf['compiled_template_cache_language'] = false;

// the local data directory is used to store data such as compiled templates
// or other plugin variables etc
$conf['local_data_dir'] = dirname(dirname(__FILE__)).'/_data';

?>
