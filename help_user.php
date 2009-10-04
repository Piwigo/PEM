<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2009 PEM Team - http://home.gna.org/pem            |
// +-----------------------------------------------------------------------+
// | last modifier : $Author$
// | revision      : $Revision$
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

$tpl->set_filename('page', 'page.tpl');

if (!isset($user['id']))
{
  message_die('You must be connected to read help user');
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$help_user = '';

$selected_language_file = $root_path.'language/'.$_SESSION['language']['code'].'/help_user.html';
$default_language_file = $root_path.'language/'.$conf['default_language'].'/help_user.html';

if (is_file($selected_language_file))
{
  $help_user = file_get_contents($selected_language_file);
}
elseif (is_file($default_language_file))
{
  $help_user = file_get_contents($default_language_file);
}

$tpl->assign('main_content', $help_user);
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();



?>
