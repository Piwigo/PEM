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

  define( 'PUN_ROOT', $root_path . '../forum/' );
  define( 'ROOT', '/pwg/mods/' );
  
  define( 'EXT_TABLE', 'pwg_extensions' );
  define( 'CAT_TABLE' , 'pwg_categories' );
  define( 'VER_TABLE', 'pwg_versions' );
  define( 'REV_TABLE', 'pwg_revisions' );
  define( 'COMP_TABLE', 'pwg_revisions_compatibilities' );
  define( 'EXT_CAT_TABLE', 'pwg_extensions_categories' );
  
  define( 'EXTENSIONS_PER_PAGE', 3 );
  define( 'LAST_ADDED_EXTS_COUNT', 5 );
  
  define( 'PUN_TURN_OFF_MAINT', 1 );
  define( 'PUN_QUIET_VISIT', 1 );
  
  define( 'EXTENSIONS_DIR', 'upload/' );
?>
