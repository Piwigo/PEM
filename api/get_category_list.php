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
$root_path = '../';
require_once($root_path.'include/common.inc.php');

$query = '
SELECT
    idx_category,
    COUNT(*) AS counter
  FROM '.EXT_CAT_TABLE.'
  GROUP BY idx_category
;';
$nb_ext_of_category = simple_hash_from_query($query, 'idx_category', 'counter');

$query = '
SELECT
    id_category AS id,
    name
  FROM '.CAT_TABLE.'
  ORDER BY name ASC
;';
$output = array_of_arrays_from_query($query);
foreach ($output as $i => $category) {
  $output[$i]['name'] = get_user_language($output[$i]['name']);
  
  $output[$i]['counter'] = 0;
  if (isset($nb_ext_of_category[ $category['id'] ])) {
    $output[$i]['counter'] = $nb_ext_of_category[ $category['id'] ];
  }
}

$format = 'json';
if (isset($_GET['format'])) {
  $format = strtolower($_GET['format']);
}

switch ($format) {
  case 'json' :
    echo json_encode($output);
    break;
  case 'php' :
    echo serialize($output);
    break;
  default :
    echo json_encode($output);
}
?>