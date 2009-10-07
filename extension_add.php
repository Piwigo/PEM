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
  message_die('You must be connected to add, modify or delete an extension.');
}

if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
{
  $authors = get_extension_authors($page['extension_id']);

  if (!in_array($user['id'], $authors) and !isAdmin($user['id']) and !isTranslator($user['id']))
  {
    message_die('You must be the extension author to modify it.');
  }
}

// Form submitted
if (isset($_POST['submit']))
{
  // Form sumbmitted for translator
  if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php' and !in_array($user['id'], $authors) and !isAdmin($user['id']))
  {
    $query = 'SELECT idx_language FROM '.EXT_TABLE.' WHERE id_extension = '.$page['extension_id'].';';
    $result = $db->query($query);
    list($def_language) = mysql_fetch_array($result);

    $query = '
DELETE
  FROM '.EXT_TRANS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
    AND idx_language IN ('.implode(',', $conf['translator_users'][$user['id']]).')
;';
    $db->query($query);

    $inserts = array();
    foreach ($_POST['extension_descriptions'] as $lang_id => $desc)
    {
      if (!in_array($lang_id, $conf['translator_users'][$user['id']]))
      {
        continue;
      }
      if ($lang_id == $def_language)
      {
        if (empty($desc))
        {
          message_die('Default description can not be empty');
        }
        $query = '
    UPDATE '.EXT_TABLE.'
      SET description = \''.$desc.'\'
      WHERE id_extension = '.$page['extension_id'].'
    ;';
        $db->query($query);
      }
      else
      {
        array_push(
          $inserts,
          array(
            'idx_extension'  => $page['extension_id'],
            'idx_language'   => $lang_id,
            'description'    => $desc,
            )
          );
      }
    }
    if (!empty($inserts))
    {
      mass_inserts(EXT_TRANS_TABLE, array_keys($inserts[0]), $inserts);
    }
    message_success('Extension successfuly added. Thank you.', 'extension_view.php?eid='.$page['extension_id']);
  }

  // Checks that all the fields have been well filled
  $required_fields = array(
    'extension_name',
    'extension_category',
    );
  
  foreach ($required_fields as $field)
  {
    if (empty($_POST[$field]))
    {
      message_die('Some fields are missing');
    }
  }

  if (empty($_POST['extension_descriptions'][@$_POST['default_description']]))
  {
    message_die('Default description can not be empty');
  }
    
  if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
  {
    // Update the extension
    $query = '
UPDATE '.EXT_TABLE.'
  SET name = \''.$_POST['extension_name'].'\',
      description = \''.$_POST['extension_descriptions'][$_POST['default_description']].'\',
      idx_language = '.$_POST['default_description'].'
  WHERE id_extension = '.$page['extension_id'].'
;';
    $db->query($query);

    $query = '
DELETE
  FROM '.EXT_TRANS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
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
      'description'  => $_POST['extension_descriptions'][$_POST['default_description']],
      'idx_language' => $_POST['default_description'],
      );
    mass_inserts(EXT_TABLE, array_keys($insert), array($insert));
    $page['extension_id'] = $db->insert_id();
  }

  // Insert translations
  $inserts = array();
  foreach ($_POST['extension_descriptions'] as $lang_id => $desc)
  {
    if ($lang_id == $_POST['default_description'] or empty($desc))
    {
      continue;
    }
    array_push(
      $inserts,
      array(
        'idx_extension'  => $page['extension_id'],
        'idx_language'   => $lang_id,
        'description'    => $desc,
        )
      );
  }
  if (!empty($inserts))
  {
    mass_inserts(EXT_TRANS_TABLE, array_keys($inserts[0]), $inserts);
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
  
  message_success('Extension successfuly added. Thank you.',
    'extension_view.php?eid='.$page['extension_id']);
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

if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
{
  $query = '
SELECT name,
       description,
       idx_language
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
  $result = $db->query($query);
  while ($row = mysql_fetch_assoc($result))
  {
    $extension['name'] = $row['name'];
    $extension['descriptions'][$row['idx_language']] = $row['description'];
    $extension['default_language'] = $row['idx_language'];
  }

  $query = '
SELECT idx_language,
       description
  FROM '.EXT_TRANS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
;';
  $result = $db->query($query);
  while($row = mysql_fetch_assoc($result))
  {
    $extension['descriptions'][$row['idx_language']] = $row['description'];
  }

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
  $descriptions = $extension['descriptions'];
  $default_language = $extension['default_language'];
}
else
{
  $name = '';
  $descriptions = array();
  $selected_categories = array();
  $default_language = $interface_languages[$conf['default_language']]['id'];
}

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

if (basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php')
{
  $f_action = 'extension_mod.php?eid='.$page['extension_id'];
}
else
{
  $f_action = 'extension_add.php';
}

$tpl->assign(
  array(
    'f_action' => $f_action,
    'translator' => basename($_SERVER['SCRIPT_FILENAME']) == 'extension_mod.php' and !in_array($user['id'], $authors) and !isAdmin($user['id']),
    'translator_languages' => isTranslator($user['id']) ? $conf['translator_users'][$user['id']] : array(),
    'extension_name' => $name,
    'descriptions' => $descriptions,
    'default_language' => $default_language,
    'extension_categories' => $tpl_extension_categories,
    )
  );

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'extension_add');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>