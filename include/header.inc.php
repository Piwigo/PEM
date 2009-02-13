<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2009 PEM Team - http://home.gna.org/pem            |
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

if (!defined('INTERNAL'))
{
  die('No right to do that, sorry. :)');
}

// Get the left nav menu

// categories
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
    id_category,
    name
  FROM '.CAT_TABLE.'
  ORDER BY name ASC
;';
$req = $db->query($query);

$categories = array();
while ($data = $db->fetch_assoc($req))
{
  array_push($categories, $data);
}

$tpl_categories = array();

// Browse the categories and display them
foreach($categories as $cat)
{
  $selected = '';
  if (isset($_SESSION['filter']['category_ids'])
      and in_array($cat['id_category'], $_SESSION['filter']['category_ids']))
  {
    $selected = 'selected="selected"';
  }

  $id = $cat['id_category'];

  array_push(
    $tpl_categories,
    array(
      'id'  => $id,
      'selected' => $selected,
      'name' => sprintf(
        '%s (%s)',
        get_user_language($cat['name']),
        isset($nb_ext_of_category[$id])
        ? $nb_ext_of_category[$id] > 1
          ? $nb_ext_of_category[$id].' extensions'
          : $nb_ext_of_category[$id].' extension'
        : 'no extension'
        ),
      )
    );
}

$tpl->assign('categories', $tpl_categories);

// Gets the current search
if (isset($_SESSION['filter']['search'])) {
  $tpl->assign('search', $_SESSION['filter']['search']);
}

// Gets the list of the available versions (allows users to filter)
$query = '
SELECT
    idx_version,
    COUNT(DISTINCT(idx_extension)) AS counter
  FROM '.COMP_TABLE.' AS c
    JOIN '.REV_TABLE.' AS r ON r.id_revision = c.idx_revision
  GROUP BY idx_version
;';
$nb_ext_of_version = simple_hash_from_query($query, 'idx_version', 'counter');

$query = '
SELECT
    id_version,
    version
  FROM '.VER_TABLE.'
;';
$versions = array_of_arrays_from_query($query);
$versions = versort($versions);
$versions = array_reverse($versions);

$tpl_versions = array();

// Displays the versions
foreach ($versions as $version)
{
  $version_id = $version['id_version'];
  $selected = '';
  if (isset($_SESSION['filter']['id_version'])
      and $_SESSION['filter']['id_version'] == $version_id)
  {
    $selected = 'selected="selected"';
  }
  
  array_push(
    $tpl_versions,
    array(
      'id' => $version_id,
      'name' => sprintf(
        '%s (%s)',
        $version['version'],
        isset($nb_ext_of_version[$version_id])
        ? $nb_ext_of_version[$version_id] > 1
          ? $nb_ext_of_version[$version_id].' extensions'
          : $nb_ext_of_version[$version_id].' extension'
        : 'no extension'
        ),
      'selected' => $selected,
      )
    );
}

// filter on authors
$query = '
SELECT
    idx_user,
    COUNT(*) AS counter
  FROM '.EXT_TABLE.'
  GROUP BY idx_user
;';
$nb_ext_of_user = simple_hash_from_query($query, 'idx_user', 'counter');

$user_infos_of = get_user_infos_of(array_keys($nb_ext_of_user));
usort($user_infos_of, 'compare_username');

$tpl_filter_users = array();
foreach ($user_infos_of as $author) {
  $id = $author['id'];

  $selected = '';
  if (isset($_SESSION['filter']['id_user'])
      and $_SESSION['filter']['id_user'] == $author['id'])
  {
    $selected = 'selected="selected"';
  }
    
  array_push(
    $tpl_filter_users,
    array(
      'id' => $id,
      'selected' => $selected,
      'name' => sprintf(
        '%s (%s)',
        $author['username'],
        isset($nb_ext_of_user[$id])
        ? $nb_ext_of_user[$id] > 1
          ? $nb_ext_of_user[$id].' extensions'
          : $nb_ext_of_user[$id].' extension'
        : 'no extension'
        ),
      )
    );
}

$tpl->assign('filter_users', $tpl_filter_users);


if (isset($conf['specific_header_filepath']))
{
  ob_start();
  include($conf['specific_header_filepath']);
  $specific_header = ob_get_contents();
  ob_end_clean();
  $tpl->assign('specific_header', $specific_header);
}

if (isset($conf['banner_filepath'])) {
  ob_start();
  include($conf['banner_filepath']);
  $banner = ob_get_contents();
  ob_end_clean();
  $tpl->assign('banner', $banner);
}

$tpl->assign('menu_versions', $tpl_versions);
$tpl->assign('title', $conf['page_title']);
$tpl->assign('action', $_SERVER['REQUEST_URI']);

if (isset($user['id']))
{
  $tpl->assign('user_is_logged', true);
  $tpl->assign('username', $user['username']);
  $tpl->assign('user_is_admin', isAdmin($user['id']));
}
else
{
  $tpl->assign('user_is_logged', false);
}

// echo '<pre>'; print_r($tpl->getTemplateVars()); echo '</pre>';
?>