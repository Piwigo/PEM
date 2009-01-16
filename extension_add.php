<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2009 PEM Team - http://home.gna.org/pem            |
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

if(!defined('INTERNAL'))
{
  define( 'INTERNAL', true );
}
$root_path = './';
require_once( $root_path . 'include/common.inc.php' );

$tpl->set_filenames(
  array(
    'page' => 'page.tpl',
    'extension_add' => 'extension_add.tpl'
  )
);
  
if (!isset($user['id']))
{
  $page['message']['is_success'] = false;
  $page['message']['message'] = l10n(
    'You must be connected to add, modify or delete an extension.'
    );
  $page['message']['go_back'] = true;
  include($root_path.'include/message.inc.php');
}

if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
{
  $extension_infos = get_extension_infos_of($page['extension_id']);

  if ($user['id'] != $extension_infos['idx_user'] and !isAdmin($user['id']))
  {
    message_die(l10n('You must be the extension author to modify it.'));
  }
}

// Form submitted
if (isset($_POST['submit']))
{
  // Checks that all the fields have been well filled
  $required_fields = array(
    'extension_name',
    'extension_description',
    'extension_category',
    );
  
  foreach ($required_fields as $field)
  {
    if (empty($_POST[$field]))
    {
      $page['message']['is_success'] = false;
      $page['message']['message'] = l10n(
        'Some fields are missing'
        );
      $page['message']['go_back'] = true;
      include($root_path.'include/message.inc.php');
    }
  }
    
  if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
  {
    // Update the extension
    $query = '
UPDATE '.EXT_TABLE.'
  SET name = \''.$_POST['extension_name'].'\',
      description = \''.$_POST['extension_description'].'\'
  WHERE id_extension = '.$page['extension_id'].'
;';
    $db->query($query);

    $query = '
DELETE
  FROM '.EXT_CAT_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
;';
    $db->query($query);
  }
  else
  {
    // Inserts the extension (need to be done before the other includes, to
    // retrieve the insert id
    $insert = array(
      'idx_user'   => $user['id'],
      'name'         => $_POST['extension_name'],
      'description'  => $_POST['extension_description'],
      );
    mass_inserts(EXT_TABLE, array_keys($insert), array($insert));
    $page['extension_id'] = $db->insert_id();
  }
  
  // Inserts the extensions <-> categories link
  $inserts = array();
  foreach ($_POST['extension_category'] as $category)
  {
    array_push(
      $inserts,
      array(
        'idx_category'   => $category,
        'idx_extension'  => $page['extension_id'],
        )
      );
  }
  mass_inserts(EXT_CAT_TABLE, array_keys($inserts[0]), $inserts);
  
  $page['message']['is_success'] = true;
  $page['message']['message'] = l10n(
    'Extension successfuly added. Thank you.'
    );
  $page['message']['redirect'] =
    'extension_view.php?eid='.$page['extension_id'];
  include($root_path.'include/message.inc.php');
}

// Get the category listing
$query = '
SELECT name,
       id_category
  FROM '.CAT_TABLE.'
  ORDER BY name ASC
;';
$req = $db->query($query);
      
$cats = array();
while($data = $db->fetch_assoc($req))
{
  array_push($cats, $data);
}

if (isset($_POST['submit']))
{
  $name = @$_POST['extension_name'];
  $description = @$_POST['extension_description'];
  $selected_categories = $_POST['extension_category'];
}
else if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
{
  $query = '
SELECT name,
       description
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
  $result = $db->query($query);
  $extension = $db->fetch_array($result);
  $extension['categories'] = array();

  $query = '
SELECT idx_category
  FROM '.EXT_CAT_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
;';
  $result = $db->query($query);

  while ($row = $db->fetch_array($result))
  {
    array_push(
      $extension['categories'],
      $row['idx_category']
      );
  }

  $selected_categories = $extension['categories'];
  $name = $extension['name'];
  $description = $extension['description'];
}
else
{
  $name = '';
  $description = '';
  $selected_categories = array();
}

$tpl->assign(
  array(
    'extension_name' => $name,
    'extension_description' => $description,
    )
  );

// Display the cats
$tpl_extension_categories = array();
foreach($cats as $cat)
{
  array_push(
    $tpl_extension_categories,
    array(
      'name' => get_user_language($cat['name']),
      'value' => $cat['id_category'],
      'checked' =>
      in_array($cat['id_category'], $selected_categories)
        ? 'checked="checked"'
        : '',
      )
    );
}
$tpl->assign('extension_categories', $tpl_extension_categories);

if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
{
  $f_action = 'extension_mod.php?eid='.$page['extension_id'];
}
else
{
  $f_action = 'extension_add.php';
}

$tpl->assign('f_action', $f_action);
  
// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'extension_add');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>