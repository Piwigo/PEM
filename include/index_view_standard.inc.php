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

$tpl->set_filename('index_view_standard', 'index_view_standard.tpl');

$page['page'] = 1;
if (isset($_GET['page'])) {
  $page['page'] = abs(intval($_GET['page']));

  if ($page['page'] == 0) {
    $page['page'] = 1;
  }
}

$revision_ids = array();
$revision_infos_of = array();
$extension_ids = array();
$extension_infos_of = array();
$author_ids = array();
$author_infos_of = array();

// retrieve N last updated extensions, filtered on the user version
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
  GROUP BY idx_extension';
if (isset($_SESSION['filter']['search'])) {
  $query.= '
  ORDER BY FIND_IN_SET(idx_extension, "'.$page['filtered_extension_ids_string'].'")';
}
else {
  $query.= '
  ORDER BY max_date DESC';
}
$query.= '
;';

$all_revision_ids = array_from_query($query, 'id_revision');
$nb_total = count($all_revision_ids);

$first = ($page['page'] - 1) * $conf['extensions_per_page'];

$revision_ids = array_slice(
  $all_revision_ids,
  $first,
  $conf['extensions_per_page']
  );

if (count($revision_ids) == 0)
{
  message_die(
    'No extension match your filter',
    'Most recent extensions',
    false
    );
}

$versions_of = get_versions_of_revision($revision_ids);
$languages_of = get_languages_of_revision($revision_ids);

// retrieve revisions information
$revision_infos_of = get_revision_infos_of($revision_ids);
$extension_ids = array_unique(
  array_from_subfield(
    $revision_infos_of,
    'idx_extension'
    )
  );

$extension_infos_of = get_extension_infos_of($extension_ids);
$download_of_extension = get_download_of_extension($extension_ids);
$categories_of_extension = get_categories_of_extension($extension_ids);
$tags_of_extension = get_tags_of_extension($extension_ids);

$revisions = array();
foreach ($revision_ids as $revision_id)
{
  $extension_id = $revision_infos_of[$revision_id]['idx_extension'];
  $authors = get_extension_authors($extension_id);
  $screenshot_infos = get_extension_screenshot_infos($extension_id);

  array_push(
    $revisions,
    array(
      'id' => $revision_id,
      'extension_id' => $extension_id,
      'extension_name' => $extension_infos_of[$extension_id]['name'],
      'rating_score' => generate_static_stars($extension_infos_of[$extension_id]['rating_score']),
      'nb_reviews' => !empty($extension_infos_of[$extension_id]['nb_reviews']) ? sprintf(l10n('%d reviews'), $extension_infos_of[$extension_id]['nb_reviews']) : null,
      'about' => nl2br(
        htmlspecialchars(
          strip_tags($extension_infos_of[$extension_id]['description'])
          )
        ),
      'authors' => array_combine($authors, get_author_name($authors)),
      'name' => $revision_infos_of[$revision_id]['version'],
      'compatible_versions' => implode(', ', $versions_of[$revision_id]),
      'languages' => isset($languages_of[$revision_id]) ?
          $languages_of[$revision_id] : array(),
      'description' => nl2br(
        htmlspecialchars(
          strip_tags($revision_infos_of[$revision_id]['description'])
          )
        ),
      'date' => date('Y-m-d', $revision_infos_of[$revision_id]['date']),
      'thumbnail_src' => $screenshot_infos
        ? $screenshot_infos['thumbnail_src']
        : null,
      'screenshot_url' => $screenshot_infos
        ? $screenshot_infos['screenshot_url']
        : null,
      'revision_url' => sprintf(
        'extension_view.php?eid=%u&amp;rid=%u#rev%u',
        $extension_id,
        $revision_id,
        $revision_id
        ),
      'downloads' => isset($download_of_extension[$extension_id]) ?
                       $download_of_extension[$extension_id] : 0,
      'categories' => $categories_of_extension[$extension_id],
      'tags' => $tags_of_extension[$extension_id],
      )
    );
}
$tpl->assign('revisions', $revisions);

$tpl->assign(
  'navbar',
  create_pagination_bar(
    'index.php',
    get_nb_pages(
      $nb_total,
      $conf['extensions_per_page']
      ),
    $page['page'],
    'page'
    )
  );

$tpl->assign(
  'nb_total',
  $nb_total
  );

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'index_view_standard');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
?>