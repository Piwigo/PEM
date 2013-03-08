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

// search
if (isset($_POST['reset']))
{
  unset($_POST);
}

$join = $where = array();
if (isset($_POST['category']) and $_POST['category']!=-1)
{
  $join[] = '
    INNER JOIN '.EXT_CAT_TABLE.' AS cat
      ON cat.idx_extension = ext.id_extension
      AND cat.idx_category = '.$_POST['category'];
    
  $tpl->assign('filter_category', $_POST['category']);
}
if (isset($_POST['version']) and $_POST['version']!=-1)
{
  $join[] = '
    INNER JOIN '.REV_TABLE.' AS rev
      ON rev.idx_extension = ext.id_extension
    INNER JOIN '.COMP_TABLE.' AS comp
      ON comp.idx_revision = rev.id_revision
      AND comp.idx_version = '.$_POST['version'];
    
  $tpl->assign('filter_version', $_POST['version']);
}
if (!empty($_POST['name']))
{
  $where[] = 'LOWER(ext.name) LIKE "%'.strtolower($_POST['name']).'%"';
  
  $tpl->assign('filter_name', stripslashes($_POST['name']));
}


// get extensions
$query = '
SELECT
    ext.id_extension,
    ext.name,
    ext.idx_language AS main_language,
    trans.idx_language AS other_language
  FROM '.EXT_TABLE.' AS ext
    LEFT JOIN '.EXT_TRANS_TABLE.' AS trans
      ON trans.idx_extension = ext.id_extension
    '.implode("\n    ", $join);
if (count($where))
{
  $query.= '
  WHERE
    '.implode("\n    AND ", $where);
}
$query.= '
  ORDER BY name ASC
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

$tpl->assign('extensions', $extension_languages);


// categories
$query = '
SELECT id_category, name   
  FROM '.CAT_TABLE.' AS c
  ORDER BY name ASC
;';
$tpl->assign('categories', simple_hash_from_query($query, 'id_category', 'name'));

// versions
$query = '
SELECT id_version, version
  FROM '.VER_TABLE.'
  ORDER BY version DESC
;';
$tpl->assign('versions', simple_hash_from_query($query, 'id_version', 'version'));

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'extensions_translations');
$tpl->pparse('page');
?>