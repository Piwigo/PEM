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
$root_path = './';
require_once($root_path.'include/common.inc.php');

$template->set_file('my', 'my.tpl');

// Gets the total information about the extensions
$query = '
SELECT id_extension,
       name
  FROM '.EXT_TABLE.'
  WHERE idx_author = \''.$user['id'].'\'
  ORDER BY name DESC
;';
$req = $db->query($query);

$template->set_block('my', 'extension', 'Textension');
while ($data = $db->fetch_assoc($req))
{
  $template->set_var(
    array(
      'NAME' => htmlspecialchars(strip_tags($data['name'])),
      'ID' => $data['id_extension']
      )
    );
  $template->parse('Textension', 'extension', true);
}

build_header();
$template->parse('output', 'my', true);
build_footer();
?>