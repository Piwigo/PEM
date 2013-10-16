<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2013 PEM Team - http://piwigo.org                  |
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

// if a category_id is given, it will say the number of extensions available
// for each version, but all versions are returned.

$category_id = null;
if (isset($_GET['category_id'])) {
  $category_id = $_GET['category_id'];
  if ($category_id != abs(intval($category_id))) {
    die('unexpected category identifier');
  }
}

$query = '
SELECT
    idx_version,
    COUNT(DISTINCT(r.idx_extension)) AS counter
  FROM '.COMP_TABLE.' AS c
    JOIN '.REV_TABLE.' AS r ON r.id_revision = c.idx_revision';
if (isset($category_id)) {
  $query.= '
    JOIN '.EXT_TABLE.' AS e ON e.id_extension = r.idx_extension
    JOIN '.EXT_CAT_TABLE.'  AS ec ON ec.idx_extension = e.id_extension
  WHERE idx_category = '.$category_id.'
';
}
$query.= '
  GROUP BY idx_version
;';
$nb_ext_of_version = simple_hash_from_query($query, 'idx_version', 'counter');

$query = '
SELECT
    id_version,
    version
  FROM '.VER_TABLE.'
;';
$versions = array_reverse(
  versort(
    array_of_arrays_from_query(
      $query
      )
    )
  );

$output_versions = array();

foreach ($versions as $version) {
  $id_version = $version['id_version'];
  
  array_push(
    $output_versions,
    array(
      'id' => $id_version,
      'name' => $version['version'],
      'nb_extensions' => isset($nb_ext_of_version[$id_version]) ? $nb_ext_of_version[$id_version] : 0,
      )
    );
}

$format = 'json';
if (isset($_GET['format'])) {
  $format = strtolower($_GET['format']);
}

switch ($format) {
  case 'json' :
    echo json_encode($output_versions);
    break;
  case 'php' :
    echo serialize($output_versions);
    break;
  default :
    echo json_encode($output_versions);
}
?>
