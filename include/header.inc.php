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
$page_title = array();

// categories
$query = '
SELECT
    idx_category,
    COUNT(1) AS counter
  FROM '.EXT_CAT_TABLE.'
  WHERE idx_extension IN (
    SELECT DISTINCT(idx_extension) FROM '.REV_TABLE.'
  )
  GROUP BY idx_category
;';
$nb_ext_of_category = simple_hash_from_query($query, 'idx_category', 'counter');

$query = '
SELECT
    id_category,
    c.name AS default_name,
    ct.name    
  FROM '.CAT_TABLE.' AS c
  LEFT JOIN '.CAT_TRANS_TABLE.' AS ct
    ON c.id_category = ct.idx_category
    AND ct.idx_language = \''.get_current_language_id().'\'
  ORDER BY name ASC
;';
$req = $db->query($query);

$categories = array();
while ($data = $db->fetch_assoc($req))
{
  if (empty($data['name']))
  {
    $data['name'] = $data['default_name'];
  }
  array_push($categories, $data);
}

$tpl_categories = array();

// Browse the categories and display them
foreach($categories as $cat)
{
  $selected = false;
  if ( isset($_SESSION['filter']['category_ids']) and $cat['id_category'] == $_SESSION['filter']['category_ids'][0] )
  {
    array_push($page_title, l10n('Category').': '.$cat['name']);
    $selected = true;
  }

  $id = $cat['id_category'];

  array_push(
    $tpl_categories,
    array(
      'id'  => $id,
      'selected' => $selected,
      'name' => $cat['name'],
      'count' => !empty($nb_ext_of_category[$id]) ? $nb_ext_of_category[$id] : 0,
      )
    );
}

$tpl->assign('categories', $tpl_categories);

$query = '
SELECT COUNT(1)
  FROM '.EXT_TABLE.'
  WHERE id_extension IN (
    SELECT DISTINCT(idx_extension) FROM '.REV_TABLE.'
  )
;';
list($total_extensions) = $db->fetch_row($db->query($query));

$tpl->assign(array(
  'total_extensions' => $total_extensions,
  'cat_is_home' => empty($_SESSION['filter']['category_ids']),
  ));

// Gets the current search
if (isset($_SESSION['filter']['search'])) {
  array_push($page_title, l10n('Search').': '.$_SESSION['filter']['search']);
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
  if ( isset($_SESSION['filter']['id_version']) and $_SESSION['filter']['id_version'] == $version_id )
  {
    array_push($page_title, l10n('Version').': '.$version['version']);
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
          ? $nb_ext_of_version[$version_id].' '.l10n('extensions')
          : $nb_ext_of_version[$version_id].' '.l10n('extension')
        : l10n('no extension')
        ),
      'selected' => $selected,
      )
    );
}

$tpl->assign('menu_versions', $tpl_versions);

// filter on authors
/*$query = '
SELECT idx_user, SUM(counter) AS counter 
  FROM (
    SELECT
        idx_user,
        COUNT(*) AS counter
      FROM '.EXT_TABLE.'
      GROUP BY idx_user
    UNION ALL
    SELECT
        idx_user,
        COUNT(*) AS counter
      FROM '.AUTHORS_TABLE.'
      GROUP BY idx_user
  ) AS t
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
          ? $nb_ext_of_user[$id].' '.l10n('extensions')
          : $nb_ext_of_user[$id].' '.l10n('extension')
        : l10n('no extension')
        ),
      )
    );
}

$tpl->assign('filter_users', $tpl_filter_users);*/

// most used tags
$query = '
SELECT
    COUNT(1) AS count,
    id_tag,
    t.name AS default_name,
    tt.name
  FROM '.EXT_TAG_TABLE.' AS et
    LEFT JOIN '.TAG_TABLE.' AS t
      ON t.id_tag = et.idx_tag
    LEFT JOIN '.TAG_TRANS_TABLE.' AS tt
      ON t.id_tag = tt.idx_tag
      AND tt.idx_language = \''.get_current_language_id().'\'
  WHERE idx_extension IN (
    SELECT DISTINCT(idx_extension) FROM '.REV_TABLE.'
  )
  GROUP BY id_tag
  ORDER BY count DESC
  LIMIT 30
;';
$tpl_tags = array_of_arrays_from_query($query, 'id_tag');

if (count($tpl_tags))
{
  $counts = array_map(create_function('$v', 'return $v["count"];'), $tpl_tags);
  $adapt_range = create_function('$v', 'return ('.max($counts).'-'.min($counts).' != 0) ? ($v-'.min($counts).')/(2*('.max($counts).'-'.min($counts).'))+1 : 1;');

  $i = 0;
  foreach($tpl_tags as $tag)
  {
    if (empty($tag['name']))
    {
      $tag['name'] = $tag['default_name'];
    }
  
    $selected = false;
    if ( isset($_SESSION['filter']['tag_ids']) and $_SESSION['filter']['tag_ids'][0] == $tag['id_tag'] )
    {
      $selected = true;
      array_push($page_title, l10n('Tag').': '.$tag['name']);
    }
    
    $tag['size'] = $adapt_range($tag['count']);
    $tag['url'] = 'index.php?tid='.$tag['id_tag'];
    $tag['selected'] = $selected;
    
    if ($i<10) $tpl->append('tags', $tag);
    else $tpl->append('more_tags', $tag);
    $i++;
  }
}


if (isset($conf['specific_header_filepath']))
{
  ob_start();
  include($root_path.$conf['specific_header_filepath']);
  $specific_header = ob_get_contents();
  ob_end_clean();
  $tpl->assign('specific_header', $specific_header);
}

if (isset($conf['banner_filepath'])) {
  ob_start();
  include($root_path.$conf['banner_filepath']);
  $banner = ob_get_contents();
  ob_end_clean();
  $tpl->assign('banner', $banner);
}

$tpl->assign('page_title', !empty($page_title) ? implode(', ', $page_title) : l10n('Most recent extensions'));
$tpl->assign('title', $conf['page_title']);
$tpl->assign('action', !empty($_SESSION['filter']['category_ids']) ? 'index.php?cid='.$_SESSION['filter']['category_ids'][0] : 'index.php');

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

// echo '<pre>'; print_r($tpl->get_template_vars()); echo '</pre>';
?>
