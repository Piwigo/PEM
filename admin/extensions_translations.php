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
$root_path = './../';
require_once($root_path . 'include/common.inc.php');
require_once( $root_path . 'include/functions_admin.inc.php' );
require_once( $root_path . 'admin/init.inc.php' );

$tpl->set_filenames(
  array(
    'page' => 'admin/page.tpl',
    'extensions_translations' => 'admin/extensions_translations.tpl'
  )
);

// Are there extension without a single revision?
$query = '
SELECT
    id_extension,
    name,
    ext.idx_language AS main_language,
    trans.idx_language AS other_language
  FROM '.EXT_TABLE.' AS ext
    LEFT JOIN '.EXT_TRANS_TABLE.' AS trans
      ON trans.idx_extension = ext.id_extension
    INNER JOIN '.REV_TABLE.' AS rev
      ON rev.idx_extension = ext.id_extension
  GROUP BY CONCAT(other_language, rev.idx_extension)
  ORDER BY date DESC
;';
$result = $db->query($query);

$extension_languages = array();
while ($row = $db->fetch_assoc($result))
{
  if (empty($extension_languages[ $row['id_extension'] ]))
  {
    $extension_languages[ $row['id_extension'] ] = array(
      'id' => $row['id_extension'],
      'name' => $row['name'],
      'main' => $row['main_language'],
      'all' => array($row['main_language']),
      );
  }
  
  if ( !empty($row['other_language']) and 
    !in_array($row['other_language'], $extension_languages[ $row['id_extension'] ]['all']) 
  )
  {
    $extension_languages[ $row['id_extension'] ]['all'][] = $row['other_language'];
  }
}

foreach ($extension_languages as $row)
{
  if (count($row['all']) == count($interface_languages))
  {
    continue;
  }
  
  $tpl->append('extensions', $row);
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'extensions_translations');
$tpl->pparse('page');
?>