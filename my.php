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
$root_path = './';
require_once($root_path.'include/common.inc.php');

$tpl->set_filenames(
  array(
    'page' => 'page.tpl',
    'my' => 'my.tpl'
  )
);

// Get owned extensions
$query = '
SELECT id_extension
  FROM '.EXT_TABLE.'
  WHERE idx_user = \''.$user['id'].'\'
  ORDER BY name ASC
;';
$my_extension_ids = query2array($query, null, 'id_extension');

// Get other extensions
$query = '
SELECT id_extension
  FROM '.EXT_TABLE.' AS ext
  INNER JOIN '.AUTHORS_TABLE.' AS aut
    ON ext.id_extension = aut.idx_extension
  WHERE aut.idx_user = \''.$user['id'].'\'
  ORDER BY name ASC
;';
$other_extension_ids = query2array($query, null, 'id_extension');

// Gets the total information about the extensions
$extension_ids = array_merge($other_extension_ids, $my_extension_ids);

if (count($extension_ids) > 0)
{
  $query = '
SELECT * FROM (
  SELECT
      id_revision,
      version,
      idx_extension
    FROM '.REV_TABLE.'
    WHERE idx_extension IN ('.implode(',',$extension_ids).')
    ORDER BY date DESC 
  ) AS t
  GROUP BY t.idx_extension
;';
  $result = $db->query($query);

  $revision_of = $revision_ids_of = array();
  while ($row = $db->fetch_array($result))
  {
    $revision_of[ $row['idx_extension'] ] = $row['version'];
    $revision_ids_of[ $row['idx_extension'] ] = $row['id_revision'];
  }

  $extension_infos_of = get_extension_infos_of($extension_ids);
  $download_of_extension = get_download_of_extension($extension_ids);
  $versions_of_revision = get_versions_of_revision(array_values($revision_ids_of));

  $query = '
SELECT 
    COUNT(rate) AS total,
    idx_extension
  FROM '.RATE_TABLE.'
  WHERE idx_extension IN ('.implode(',',$extension_ids).')
  GROUP BY idx_extension
;';
  $total_rates_of_extension = query2array($query, 'idx_extension', 'total');

  foreach ($extension_ids as $extension_id)
  {
    $extension = array(
      'id' => $extension_id,
      'name' => htmlspecialchars(strip_tags($extension_infos_of[$extension_id]['name'])),
      'rating_score' => generate_static_stars($extension_infos_of[$extension_id]['rating_score'],0),
      'total_rates' => @$total_rates_of_extension[$extension_id],
      'nb_reviews' => $extension_infos_of[$extension_id]['nb_reviews'],
      'nb_downloads' => $download_of_extension[$extension_id],
      'revision' => @$revision_of[$extension_id],
      'last_version' => @$versions_of_revision[ $revision_ids_of[$extension_id] ][0],
      );
      
      if (in_array($extension_id, $my_extension_ids))
      {
        $tpl->append('extensions', $extension);
      }
      else
      {
        $tpl->append('other_extensions', $extension);
      }
  }
}


// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'my');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>
