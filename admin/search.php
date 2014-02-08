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
$root_path = './../';
require_once($root_path . 'include/common.inc.php');
require_once( $root_path . 'include/functions_admin.inc.php' );
require_once( $root_path . 'admin/init.inc.php' );

$tpl->set_filenames(
  array(
    'page' => 'admin/page.tpl',
    'search' => 'admin/search.tpl'
  )
);

if (isset($_POST['compatibility_check']))
{
  $compatibles = get_extension_ids_for_version($_POST['version0']);
  !empty($compatibles) or $compatibles=array(0);
  
  $query = '
SELECT id_extension
  FROM '.EXT_TABLE.'
  WHERE id_extension NOT IN('. implode(',', $compatibles) .')
;';
  $id_extensions = query2array($query, null, 'id_extension');
  
  if (!empty($id_extensions))
  {
    $query = '
SELECT
    id_extension,
    name
  FROM '.EXT_TABLE.'
  WHERE id_extension IN('. implode(',', $id_extensions) .')
;';
    $extensions = query2array($query);
  }
  else
  {
    $extensions = array();
  }
  
  $tpl->assign(array(
    'extensions' => $extensions,
    'version0' => $_POST['version0'],
   ));
}

if (isset($_POST['inter_compatibility_check']))
{
  $compatibles = get_extension_ids_for_version($_POST['version1']);
  $compatibles2 = get_extension_ids_for_version($_POST['version2']);
  !empty($compatibles2) or $compatibles2=array(0);
  
  $query = '
SELECT id_extension
  FROM '.EXT_TABLE.'
  WHERE id_extension NOT IN('. implode(',', $compatibles2) .')
;';
  $incompatibles = query2array($query, null, 'id_extension');
  
  $id_extensions = array_intersect($compatibles, $incompatibles);
  
  if (!empty($id_extensions))
  {
    $query = '
SELECT
    id_extension,
    name
  FROM '.EXT_TABLE.'
  WHERE id_extension IN('. implode(',', $id_extensions) .')
;';
    $extensions = query2array($query);
  }
  else
  {
    $extensions = array();
  }
  
  $tpl->assign(array(
    'inter_extensions' => $extensions,
    'version1' => $_POST['version1'],
    'version2' => $_POST['version2'],
   ));
}


// get versions
$query = '
SELECT
    id_version,
    version
  FROM '.VER_TABLE.'
;';
$versions = array_reverse(
  versort(
    query2array($query)
    )
  );

$tpl_versions = array();

// Displays the versions
foreach ($versions as $version)
{
  array_push(
    $tpl_versions,
    array(
      'id' => $version['id_version'],
      'name' => $version['version'],
      )
    );
}

$tpl->assign('versions', $tpl_versions);
$tpl->assign('f_action', 'search.php');

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'search');
$tpl->parse('page');
$tpl->p();
?>
