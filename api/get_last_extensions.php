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

$page['nb_extensions'] = 10;

// TODO: shouldn't this web API method be in get_revision_list.php? (with
// sort/limit additional parameters)

// TODO: merge this code with filtered sets in include/common.inc.php
$filtered_sets = array();
if (isset($_GET['categories'])) {
  $categories = $_GET['categories'];
  
  if (!preg_match('/^\d+(,\d+)*$/', $categories)) {
    die('unexpected categories identifier');
  }

  $filtered_sets['categories'] = get_extension_ids_for_categories(explode(',', $categories));
}

if (count($filtered_sets) > 0) {
  $page['filtered_extension_ids'] = array_shift($filtered_sets);
  foreach ($filtered_sets as $set) {
    $page['filtered_extension_ids'] = array_intersect(
      $page['filtered_extension_ids'],
      $set
      );
  }

  $page['filtered_extension_ids'] = array_unique(
    $page['filtered_extension_ids']
    );

  $page['filtered_extension_ids_string'] = implode(
    ',',
    $page['filtered_extension_ids']
    );
}

// TODO: merge this code with what include/index_view_standard.inc.php does
// 
// retrieve N last updated extensions
$query = '
SELECT
    r.idx_extension,
    MAX(r.id_revision) AS id_revision,
    MAX(r.date) AS max_date
  FROM '.REV_TABLE.' r';
if (isset($page['filtered_extension_ids'])) {
  if (count($page['filtered_extension_ids']) > 0) {
    $query.= '
  WHERE idx_extension IN ('.$page['filtered_extension_ids_string'].')';
  }
  else {
    $query.='
  WHERE 0=1';
  }
}
$query.= '
  GROUP BY idx_extension
  ORDER BY max_date DESC
;';
$all_revision_ids = array_from_query($query, 'id_revision');

$revisions = array();

if (count($all_revision_ids) > 0) {
  $revision_ids = array_slice(
    $all_revision_ids,
    0,
    $page['nb_extensions']
    );

  // retrieve revisions information
  $revision_infos_of = get_revision_infos_of($revision_ids);
  $extension_ids = array_unique(
    array_from_subfield(
      $revision_infos_of,
      'idx_extension'
      )
    );

  $extension_infos_of = get_extension_infos_of($extension_ids);

  foreach ($revision_ids as $revision_id) {
    $eid = $revision_infos_of[$revision_id]['idx_extension'];
  
    array_push(
      $revisions,
      array(
        'name' => $extension_infos_of[$eid]['name'],
        'url' => $conf['website_url'].'/extension_view.php?eid='.$eid,
        'revision' => $revision_infos_of[$revision_id]['version'],
        )
      );
  }
}

$format = 'json';
if (isset($_GET['format'])) {
  $format = strtolower($_GET['format']);
}

switch ($format) {
  case 'json' :
    echo json_encode($revisions);
    break;
  case 'php' :
    echo serialize($revisions);
    break;
  default :
    echo json_encode($revisions);
}
?>
