<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2006 PEM Team - http://home.gna.org/pem            |
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
$query = '
SELECT id_category,
       idx_parent,
       name,
       description
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
  array_push(
    $tpl_categories,
    array(
      'url'  => 'extensions.php?category='.$cat['id_category'],
      'name' => $cat['name'],
      )
    );
}

$tpl->assign('categories', $tpl_categories);

// Gets the list of the available versions (allows users to filter)
$query = '
SELECT id_version,
       version
  FROM '.VER_TABLE.'
;';
$versions = simple_hash_from_query($query, 'id_version', 'version');
versort($versions);
$versions = array_reverse($versions, true);

$tpl_versions = array();

// Displays the versions
foreach ($versions as $version_id => $version_name)
{
  $selected = '';
  if (isset($_SESSION['id_version'])
      and $_SESSION['id_version'] == $version_id)
  {
    $selected = 'selected="selected"';
  }
  
  array_push(
    $tpl_versions,
    array(
      'id' => $version_id,
      'name' => $version_name,
      'selected' => $selected,
      )
    );
}

$tpl->assign('menu_versions', $tpl_versions);
$tpl->assign('title', $conf['page_title']);
$tpl->assign('action', $_SERVER['REQUEST_URI']);

if (isset($user['id']))
{
  $tpl->assign('user_is_logged', true);
  $tpl->assign('username', $user['username']);

  if (in_array($user['id'], $conf['admin_users']))
  {
    $tpl->assign('user_is_admin', true);
  }
}
else
{
  $tpl->assign('user_is_logged', false);
}

// echo '<pre>'; print_r($tpl->getTemplateVars()); echo '</pre>';
?>