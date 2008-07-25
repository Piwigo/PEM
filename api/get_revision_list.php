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
$root_path = '../';
require_once($root_path.'include/common.inc.php');

$required_params = array('version', 'category_id');
foreach ($required_params as $required_param) {
  if (!isset($_GET[$required_param])) {
    die('"'.$required_param.'" is a required parameter');
  }
}

$category_id = $_GET['category_id'];
$version = $_GET['version'];

$version = mysql_real_escape_string($version);

if ($category_id != abs(intval($category_id))) {
  die('unexpected category identifier');
}

$query = '
SELECT
    r.id_revision         AS revision_id,
    r.version             AS revision_name,
    e.id_extension        AS extension_id,
    e.name                AS extension_name,
    e.idx_user            AS author_id,
    e.description         AS extension_description,
    r.date                AS revision_date,
    r.url                 AS filename,
    r.description         AS revision_description
  FROM '.REV_TABLE.' AS r
    INNER JOIN '.EXT_TABLE.'      AS e  ON e.id_extension = r.idx_extension
    INNER JOIN '.COMP_TABLE.'     AS c  ON c.idx_revision = r.id_revision
    INNER JOIN '.VER_TABLE.'      AS v  ON v.id_version = c.idx_version
    INNER JOIN '.EXT_CAT_TABLE.'  AS ec ON ec.idx_extension = e.id_extension
  WHERE ec.idx_category = '.$category_id.'
    AND v.version = \''.$version.'\'
;';

$author_ids = array();
$revisions = array();
$result = $db->query($query);
while ($row = mysql_fetch_assoc($result)) {
  $row['revision_date'] = date('Y-m-d H:i:s', $row['revision_date']);
  
  $row['file_url'] = sprintf(
    '%s/%s',
    $conf['website_url'],
    get_revision_src(
      $row['extension_id'],
      $row['revision_id'],
      $row['filename']
      )
    );

  $row['download_url'] = sprintf(
    '%s/download.php?rid=%u',
    $conf['website_url'],
    $row['revision_id']
    );

  array_push($revisions, $row);
  array_push($author_ids, $row['author_id']);
}

$user_basic_infos_of = get_user_basic_infos_of($author_ids);

foreach ($revisions as $revision_index => $revision) {
  $revisions[$revision_index]['extension_author']
    = $user_basic_infos_of[ $revision['author_id'] ]['username'];
}

echo json_encode($revisions);
?>