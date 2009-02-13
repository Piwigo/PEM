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

// we want:
//
// - number of extensions
// - number of revisions
// - all contributor ids
// - number of downloads
//
// - list of 10 top contributors, with contributor id and number of revisions
// - list of 10 most recent extensions, with extension id and "x days ago"
// - list of 10 most popular extensions, with extension id and number of downloads
// - list of 10 most active extensions, with extension id and number of revisions

define('INTERNAL', true);
$root_path = '../';
require_once($root_path.'include/common.inc.php');

// +-----------------------------------------------------------------------+
// |                              functions                                |
// +-----------------------------------------------------------------------+



// +-----------------------------------------------------------------------+
// |                            reduce data set                            |
// +-----------------------------------------------------------------------+

$output = array();
$filter = array();

$category_ids = null;
if (isset($_GET['categories'])) {
  $categories = $_GET['categories'];
  
  if (!preg_match('/^\d+(,\d+)*$/', $categories)) {
    die('unexpected categories identifier');
  }

  $filter['category_ids'] = explode(',', $categories);
}

if (count($filter) > 0) {
  $filter['category_mode'] = 'and';
  if (isset($_GET['category_mode']) and $_GET['category_mode'] == 'or') {
    $filter['category_mode'] = 'or';
  }
  
  $page['filtered_extension_ids'] = get_filtered_extension_ids($filter);

  if (count($page['filtered_extension_ids']) == 0) {
    $page['filtered_extension_ids'] = array(-1);
  }

  $page['filtered_extension_ids_string'] = implode(
    ',',
    $page['filtered_extension_ids']
    );
}

// +-----------------------------------------------------------------------+
// |                         number of extensions                          |
// +-----------------------------------------------------------------------+

$query = '
SELECT
    COUNT(*)
  FROM '.EXT_TABLE;

if (count($filter) > 0) {
  $query.= '
  WHERE id_extension IN ('.$page['filtered_extension_ids_string'].')';
}
  
  $query.= '
;';
list($output['nb_extensions']) = mysql_fetch_row($db->query($query));

// +-----------------------------------------------------------------------+
// |                          number of revisions                          |
// +-----------------------------------------------------------------------+

$query = '
SELECT
    COUNT(*)
  FROM '.REV_TABLE;

if (count($filter) > 0) {
  $query.= '
  WHERE idx_extension IN ('.$page['filtered_extension_ids_string'].')';
}
  
  $query.= '
  
;';
list($output['nb_revisions']) = mysql_fetch_row($db->query($query));

// +-----------------------------------------------------------------------+
// |                         all contributor ids                           |
// +-----------------------------------------------------------------------+

$query = '
SELECT
    DISTINCT(idx_user)
  FROM '.EXT_TABLE;

if (count($filter) > 0) {
  $query.= '
  WHERE id_extension IN ('.$page['filtered_extension_ids_string'].')';
}
  
  $query.= '
;';
$contributor_ids = array_from_query($query, 'idx_user');
$output['contributor_ids'] = $contributor_ids;

// +-----------------------------------------------------------------------+
// |                          number of downloads                          |
// +-----------------------------------------------------------------------+

$query = '
SELECT
    COUNT(*)
  FROM '.DOWNLOAD_LOG_TABLE;

if (count($filter) > 0) {
  $query.= '
    JOIN '.REV_TABLE.' ON idx_revision = id_revision
  WHERE idx_extension IN ('.$page['filtered_extension_ids_string'].')';
}
  
  $query.= '
;';
list($output['nb_downloads']) = mysql_fetch_row($db->query($query));

// +-----------------------------------------------------------------------+
// |                            top contributors                           |
// +-----------------------------------------------------------------------+

$contributors = array();
$contributor_ids = array();

// with contributor id, username and number of revisions
$query = '
SELECT
    idx_user,
    COUNT(*) AS counter
  FROM '.REV_TABLE.'
   JOIN '.EXT_TABLE.' ON idx_extension = id_extension';

if (count($filter) > 0) {
  $query.= '
  WHERE id_extension IN ('.$page['filtered_extension_ids_string'].')';
}

$query.= '
  GROUP BY idx_user
  ORDER BY COUNT(*) DESC
  LIMIT 10
;';
$result = $db->query($query);

while ($row = mysql_fetch_assoc($result)) {
  array_push($contributor_ids, $row['idx_user']);
  array_push(
    $contributors,
    array(
      'id' => $row['idx_user'],
      'nb_revisions' => $row['counter'],
      )
    );
}

$author_infos_of = get_user_infos_of($contributor_ids);

foreach ($contributors as $idx => $contributor) {
  $contributor_id = $contributor['id'];
  $contributors[$idx]['username'] = $author_infos_of[$contributor_id]['username'];
}

$output['top_contributors'] = $contributors;

// +-----------------------------------------------------------------------+
// |                         most recent extensions                        |
// +-----------------------------------------------------------------------+

// with extension id, name and "x days ago"
$most_recent = array();

$query = '
SELECT
    r.idx_extension,
    e.name,
    MAX(r.id_revision) AS id_revision,
    MAX(r.date) AS max_date
  FROM '.REV_TABLE.' r
    JOIN '.EXT_TABLE.' e ON id_extension = idx_extension';
if (count($filter) > 0) {
    $query.= '
  WHERE idx_extension IN ('.$page['filtered_extension_ids_string'].')';
}
$query.= '
  GROUP BY idx_extension
  ORDER BY max_date DESC
  LIMIT 10
;';
$result = $db->query($query);
while ($row = mysql_fetch_assoc($result)) {
  $duration = sprintf('%u', (time() - $row['max_date']) / (60*60*24));
  if ($duration == 0) {
    $duration_string = 'today';
  }
  else if ($duration == 1) {
    $duration_string = 'yesterday';
  }
  else {
    $duration_string = $duration.' days';
  }
  
  array_push(
    $most_recent,
    array(
      'id' => $row['idx_extension'],
      'name' => $row['name'],
      'age' => $duration_string,
      )
    );
}

$output['most_recent'] = $most_recent;

// +-----------------------------------------------------------------------+
// |                         most popular extensions                       |
// +-----------------------------------------------------------------------+

// with extension id, name and number of downloads
$most_popular = array();

$extension_ids = array();
if (count($filter) > 0) {
  $extension_ids = $page['filtered_extension_ids'];
}
else {
  $query = '
SELECT
    id_extension
  FROM '.EXT_TABLE.'
;';
  $extension_ids = array_from_query($query, 'id_extension');
}
$download_of_extension = get_download_of_extension($extension_ids);

asort($download_of_extension);
$download_of_extension = array_slice(
  array_reverse($download_of_extension, true),
  0,
  10,
  true
  );

$extension_infos_of = get_extension_infos_of(array_keys($download_of_extension));

foreach ($download_of_extension as $eid => $nb_downloads) {
  array_push(
    $most_popular,
    array(
      'id' => $eid,
      'nb_downloads' => $nb_downloads,
      'name' => $extension_infos_of[$eid]['name'],
      )
    );
}

$output['most_popular'] = $most_popular;

// +-----------------------------------------------------------------------+
// |                         most active extensions                        |
// +-----------------------------------------------------------------------+

// with extension id, name and number of revisions
$most_active = array();

$query = '
SELECT
    r.idx_extension,
    e.name,
    COUNT(*) AS counter
  FROM '.REV_TABLE.' r
    JOIN '.EXT_TABLE.' e ON id_extension = idx_extension';
if (count($filter) > 0) {
    $query.= '
  WHERE idx_extension IN ('.$page['filtered_extension_ids_string'].')';
}
$query.= '
  GROUP BY idx_extension
  ORDER BY counter DESC
  LIMIT 10
;';

$result = $db->query($query);
while ($row = mysql_fetch_assoc($result)) {
  array_push(
    $most_active,
    array(
      'id' => $row['idx_extension'],
      'name' => $row['name'],
      'nb_revisions' => $row['counter'],
      )
    );
}

$output['most_active'] = $most_active;

// +-----------------------------------------------------------------------+
// |                         data structure output                         |
// +-----------------------------------------------------------------------+

// print_array($output); exit();

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