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
$my_extension_ids = array_from_query($query, 'id_extension');

// Get other extensions
$query = '
SELECT id_extension
  FROM '.EXT_TABLE.' AS ext
  INNER JOIN '.AUTHORS_TABLE.' AS aut
    ON ext.id_extension = aut.idx_extension
  WHERE aut.idx_user = \''.$user['id'].'\'
  ORDER BY name ASC
;';
$other_extension_ids = array_from_query($query, 'id_extension');

// Gets the total information about the extensions
$extension_ids = array_merge($other_extension_ids, $my_extension_ids);
$query = '
SELECT * FROM (
  SELECT 
      rev.version,
      rev.idx_extension,
      ver.version AS last_version
    FROM '.REV_TABLE.' AS rev
      INNER JOIN '.COMP_TABLE.' AS comp
      ON comp.idx_revision = rev.id_revision
      INNER JOIN '.VER_TABLE.' AS ver
      ON ver.id_version = comp.idx_version
    WHERE idx_extension IN ('.implode(',',$extension_ids).')
    ORDER BY date DESC 
  ) AS t
  GROUP BY t.idx_extension
;';
$revision_of = hash_from_query($query, 'idx_extension');

$extension_infos_of = get_extension_infos_of($extension_ids);
$download_of_extension = get_download_of_extension($extension_ids);

$query = '
SELECT 
    COUNT(rate) AS total,
    idx_extension
  FROM '.RATE_TABLE.'
  GROUP BY idx_extension
;';
$total_rates_of_extension = simple_hash_from_query($query, 'idx_extension', 'total');

foreach ($extension_ids as $extension_id)
{
  $extension = array(
    'id' => $extension_id,
    'name' => htmlspecialchars(strip_tags($extension_infos_of[$extension_id]['name'])),
    'rating_score' => generate_static_stars($extension_infos_of[$extension_id]['rating_score'],0),
    'total_rates' => @$total_rates_of_extension[$extension_id],
    'nb_reviews' => $extension_infos_of[$extension_id]['nb_reviews'],
    'nb_downloads' => $download_of_extension[$extension_id],
    'revision' => @$revision_of[$extension_id]['version'],
    'last_version' => @$revision_of[$extension_id]['last_version'],
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


// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'my');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>
