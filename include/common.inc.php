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

header('Content-Type: text/html; charset=iso-8859-1');
  // Hacking attempt
  if(!defined('INTERNAL'))
    die('No right to do that, sorry. :)');
    
  session_start( );
    
  require_once($root_path . 'include/constants.inc.php');
  require_once($root_path . 'include/mysql.inc.php');
  require_once($root_path . 'include/config.inc.php');
  require_once($root_path . 'include/templates.inc.php');
  require_once($root_path . 'include/functions.inc.php');
  // Intégration de punbb
  require_once(PUN_ROOT . 'include/common.php');
  
  $template = new Template($root_path . 'template');
  
  // PWG Compatibility version set
  if( isset( $_POST['compatibility_change'] ) )
  {
    // Check if the field is valid
    if( isset( $_POST['pwg_version'] ) and is_numeric( $_POST['pwg_version'] ) )
    {
      // If the field is empty, this means that the user wants to cancel
      // the compatibility version setting
      if( !empty( $_POST['pwg_version'] ) )
      {
        $_SESSION['id_version'] = intval( $_POST['pwg_version'] );
      }
      else
      {
        unset( $_SESSION['id_version'] );
      }
    }
  }
?>
