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

  define('INTERNAL', true);
  $root_path = './../';
  require_once($root_path . 'include/common.inc.php');
  require_once( $root_path . 'include/functions_admin.inc.php' );
  require_once( $root_path . 'admin/init.inc.php' );
  
  $template->set_file( 'index', 'admin/index.tpl' );
  
  // Select the revisions count
  $sql =  "SELECT COUNT(id_revision) AS revisions_count";
  $sql .= " FROM " . REV_TABLE;
  $req = $db->query( $sql );
  $data = $db->fetch_assoc( $req );
  
  $template->set_var( 'L_REVISIONS_COUNT', $data['revisions_count'] );
  
  build_admin_header();
  $template->parse( 'output', 'index', true );
  build_admin_footer();
  
?>
