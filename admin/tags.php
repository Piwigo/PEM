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
    'tags' => 'admin/tags.tpl'
  )
);

$tpl->assign('tag_form_title', l10n('Add a tag'));
$tpl->assign('tag_form_type', l10n('add'));
$tpl->assign('default_name', $interface_languages[$conf['default_language']]['id']);

if (isset($_POST['submit_add']))
{
  $insert = array(
    'name' => $_POST['name'][$_POST['default_name']],
    'idx_language' => $_POST['default_name'],
    );

  mass_inserts(
    TAG_TABLE,
    array_keys($insert),
    array($insert)
    );
  $page['tag_id'] = $db->insert_id();
}

if (isset($_POST['submit_edit']))
{
  $page['tag_id'] = $_POST['id'];

  mass_updates(
    TAG_TABLE,
    array(
      'primary' => array('id_tag'),
      'update' => array('name', 'idx_language'),
      ),
    array(
      array(
        'id_tag' => $page['tag_id'],
        'name' => $_POST['name'][$_POST['default_name']],
        'idx_language' => $_POST['default_name'],
        )
      )
    );

  $query = '
DELETE
  FROM '.TAG_TRANS_TABLE.'
  WHERE idx_tag = '.$_POST['id'].'
;';
  $db->query($query);

  $tpl->assign('f_action', 'tags.php');
  unset($_GET['edit']);
}

if (isset($_POST['submit_edit']) or isset($_POST['submit_add']))
{
  // Insert translations
  $inserts = array();
  foreach ($_POST['name'] as $lang_id => $name)
  {
    if ($lang_id == $_POST['default_name'] or empty($name))
    {
      continue;
    }
    array_push(
      $inserts,
      array(
        'idx_tag' => $page['tag_id'],
        'idx_language' => $lang_id,
        'name'         => $name,
        )
      );
  }
  if (!empty($inserts))
  {
    mass_inserts(TAG_TRANS_TABLE, array_keys($inserts[0]), $inserts);
  }
}

if (isset($_GET['edit']))
{
  $page['tag_id'] = abs(intval($_GET['edit']));
  if ($page['tag_id'] != $_GET['edit'])
  {
    message_die('edit URL parameter is incorrect', 'Error', false);
  }

  $tpl->assign('tag_form_title', l10n('Modify a tag'));
  $tpl->assign('tag_form_type', l10n('edit'));
  $tpl->assign('tag_form_expanded', true);

  $query = '
SELECT
    id_tag,
    name,
    idx_language
  FROM '.TAG_TABLE.'
  WHERE id_tag = '.$page['tag_id'].'
;';
  $row = $db->fetch_assoc($db->query($query));
  $tpl->assign('tag_id', $row['id_tag']);
  $tpl->assign('default_name', $row['idx_language']);
  $name = array($row['idx_language'] => $row['name']);

  $query = '
SELECT idx_tag,
       idx_language,
       name
  FROM '.TAG_TRANS_TABLE.'
  WHERE idx_tag = '.$page['tag_id'].'
;';
  $result = $db->query($query);
  while($row = mysql_fetch_assoc($result))
  {
    $name[$row['idx_language']] = $row['name'];
  }
  $tpl->assign('name', $name);
}

if (isset($_GET['delete']))
{
  $page['tag_id'] = abs(intval($_GET['delete']));
  if ($page['tag_id'] != $_GET['delete'])
  {
    message_die('edit URL parameter is incorrect', 'Error', false);
  }

  delete_tag($page['tag_id']);
}

// tags selection
$query = '
SELECT
    id_tag,
    name
  FROM '.TAG_TABLE.'
  ORDER BY name ASC
;';
$req = $db->query($query);

$tpl_tags = array();
while ($cat = $db->fetch_assoc($req))
{
  array_push(
    $tpl_tags,
    array(
      'id' => $cat['id_tag'],
      'name' => $cat['name'],
      )
    );
}

$tpl->assign('tags', $tpl_tags);
$tpl->assign('f_action', 'tags.php');

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'tags');
$tpl->parse('page');
$tpl->p();
?>
