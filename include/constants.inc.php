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

define( 'EXT_TABLE',     $conf['db_params']['tables_prefix'].'extensions' );
define( 'CAT_TABLE' ,    $conf['db_params']['tables_prefix'].'categories' );
define( 'VER_TABLE',     $conf['db_params']['tables_prefix'].'versions' );
define( 'REV_TABLE',     $conf['db_params']['tables_prefix'].'revisions' );
define( 'COMP_TABLE',    $conf['db_params']['tables_prefix'].'revisions_compatibilities' );
define( 'EXT_CAT_TABLE', $conf['db_params']['tables_prefix'].'extensions_categories' );
define( 'USER_INFOS_TABLE', $conf['db_params']['tables_prefix'].'user_infos' );
define( 'LINKS_TABLE', $conf['db_params']['tables_prefix'].'links' );
define( 'AUTHORS_TABLE', $conf['db_params']['tables_prefix'].'authors' );
define( 'LANG_TABLE', $conf['db_params']['tables_prefix'].'languages' );
define( 'REV_LANG_TABLE', $conf['db_params']['tables_prefix'].'revisions_languages' );
define( 'EXT_TRANS_TABLE', $conf['db_params']['tables_prefix'].'extensions_translations' );
define( 'REV_TRANS_TABLE', $conf['db_params']['tables_prefix'].'revisions_translations' );
define( 'CAT_TRANS_TABLE', $conf['db_params']['tables_prefix'].'categories_translations' );

define( 'USERS_TABLE', $conf['users_table'] );

define(
  'DOWNLOAD_LOG_TABLE',
  $conf['db_params']['tables_prefix'].'download_log'
  );

  
define( 'EXTENSIONS_PER_PAGE', 3 );
define( 'PUN_TURN_OFF_MAINT', 1 );
define( 'PUN_QUIET_VISIT', 1 );
?>
