<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2006 PEM Team - http://home.gna.org/pem            |
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

define( 'INTERNAL', true );
$root_path = './';
require_once( $root_path . 'include/common.inc.php' );
  
if (!isset($user['id']))
{
  message_die(l10n('You must be connected to add an extension'));
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
      message_die( 'Vous n\'avez pas rempli tous les champs.' );
    }
  }
    
  // Escapes the array by using the mysql_escape_array( ) function
  $_POST = escape_array($_POST);

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
  
  message_success(
    l10n('Extension successfuly added. Thank you.'),
    'extension_view.php?eid='.$page['extension_id']
    );
}

// Display the element adding form
$template->set_file('extension_add', 'extension_add.tpl');

// Get the category listing
$query = '
SELECT name,
       id_category,
       idx_parent
  FROM '.CAT_TABLE.'
  ORDER BY name ASC
;';
$req = $db->query($query);
      
// We need to display only categories that don't have sub-categories
$cats = array();
$subcats = array();
while($data = $db->fetch_assoc($req))
{
  if (!empty($data['idx_parent']))
  {
    $subcats[] = $data['idx_parent'];
  }

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

$template->set_var(
  array(
    'EXTENSION_NAME' => $name,
    'EXTENSION_DESCRIPTION' => $description,
    )
  );

// Display the cats
$template->set_block('extension_add', 'extension_category', 'Textension_category');
foreach($cats as $cat)
{
  if (!in_array($cat['id_category'], $subcats))
  {
    $template->set_var(
      array(
        'EXTENSION_CAT_NAME' => $cat['name'],
        'EXTENSION_CAT_VALUE' => $cat['id_category'],
        'CHECKED' =>
          in_array($cat['id_category'], $selected_categories)
          ? 'checked="checked"'
          : '',
        )
      );
    $template->parse('Textension_category', 'extension_category', true);
  }
}

if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
{
  $template->set_var(
    array(
      'F_ACTION' => 'extension_mod.php?eid='.$page['extension_id'],
      )
    );
}
else
{
  $template->set_var(
    array(
      'F_ACTION' => 'extension_add.php',
      )
    );
}

build_header();
$template->parse('output', 'extension_add', true);
build_footer();

?>