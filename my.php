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

define('INTERNAL', true);
$root_path = './';
require_once($root_path.'include/common.inc.php');

// Gets the total information about the extensions
$query = '
SELECT
    id_extension,
    name
  FROM '.EXT_TABLE.'
  WHERE idx_user = \''.$user['id'].'\'
  ORDER BY name DESC
;';
$req = $db->query($query);

$tpl_extensions = array();
while ($data = $db->fetch_assoc($req))
{
  array_push(
    $tpl_extensions,
    array(
      'name' => htmlspecialchars(strip_tags($data['name'])),
      'id' => $data['id_extension']
      )
    );
}
$tpl->assign('extensions', $tpl_extensions);

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign('main_content', 'my.jtpl');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->display('page.jtpl');
?>