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
    'extension_authors' => 'extension_authors.tpl'
  )
);

// +-----------------------------------------------------------------------+
// |                           Initialization                              |
// +-----------------------------------------------------------------------+

if (!isset($user['id']))
{
  message_die('You must be connected to add, modify or delete an extension.');
}

// We need a valid extension
$page['extension_id'] =
  (isset($_GET['eid']) and is_numeric($_GET['eid']))
  ? $_GET['eid']
  : '';

if (empty($page['extension_id']))
{
  message_die('Incorrect extension identifier');
}

$extension_infos = get_extension_infos_of($page['extension_id']);

if ($user['id'] != $extension_infos['idx_user'] and !isAdmin($user['id']))
{
  message_die('You must be the extension author to modify it.');
}

$query = '
SELECT name
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
$result = $db->query($query);

if ($db->num_rows($result) == 0)
{
  message_die('Incorrect extension identifier');
}
list($page['extension_name']) = $db->fetch_array($result);

// +-----------------------------------------------------------------------+
// |                           Form submission                             |
// +-----------------------------------------------------------------------+

if (isset($_POST['submit_add']))
{
  $query = '
SELECT '.$conf['user_fields']['id'].' AS id
  FROM '.USERS_TABLE.'
  WHERE '.$conf['user_fields']['id'].' = '. $db->escape($_POST['author_select']) .'
;';
  list($author_id) = $db->fetch_array($db->query($query));

  if (empty($author_id))
  {
    $page['errors'][] = l10n('This user does not exist in database.');
  }
  else
  {
    $authors = get_extension_authors($page['extension_id']);

    if (!in_array($author_id, $authors))
    {
      $query = '
INSERT INTO '.AUTHORS_TABLE.' (idx_extension, idx_user)
  VALUES ('.$page['extension_id'].', '.$author_id.')
;';
      $db->query($query);
    }
  }
}

if (isset($_GET['delete']))
{
  $author = intval($_GET['delete']);

  if ($author > 0)
  {
    $query = '
DELETE FROM '.AUTHORS_TABLE.'
  WHERE idx_user = '.$author.'
  AND idx_extension = '.$page['extension_id'].'
;';
    $db->query($query);
  }
}

if (isset($_GET['owner']))
{
  $author = intval($_GET['owner']);

  if ($author > 0)
  {
    $query = '
UPDATE '.EXT_TABLE.'
  SET idx_user = '.$author.'
  WHERE id_extension = '.$page['extension_id'].'
;';
    $db->query($query);

    $query = '
DELETE FROM '.AUTHORS_TABLE.'
  WHERE idx_user = '.$author.'
  AND idx_extension = '.$page['extension_id'].'
;';
    $db->query($query);

    $query = '
INSERT INTO '.AUTHORS_TABLE.' (idx_extension, idx_user)
  VALUES ('.$page['extension_id'].', '.$extension_infos['idx_user'].')
;';
    $db->query($query);

    $extension_infos['idx_user'] = $author;
  }
}


// +-----------------------------------------------------------------------+
// |                            Form display                               |
// +-----------------------------------------------------------------------+

$authors = get_extension_authors($page['extension_id']);

foreach ($authors as $author_id)
{
  $author = array(
    'ID' => $author_id,
    'NAME' => get_author_name($author_id),
    'OWNER' => $author_id == $extension_infos['idx_user'],
    'u_delete' => 'extension_authors.php?eid='.$page['extension_id'].
                  '&amp;delete='.$author_id,
    );

  if (isAdmin($user['id']))
  {
    $author['u_owner'] = 'extension_authors.php?eid='.$page['extension_id'].
                  '&amp;owner='.$author_id;
  }

  $tpl->append('authors', $author);
}

// Get all user list
$query = '
SELECT '.$conf['user_fields']['id'].' AS id,
       '.$conf['user_fields']['username'].' AS username
  FROM '.USERS_TABLE.'
  ORDER BY username
;';
$result = $db->query($query);

$users = array(0 => '');
while ($row = mysql_fetch_assoc($result))
{
  if (!empty($row['username']))
  {
    $users[$row['id']] = $row['username'];
  }
}

$tpl->assign(
  array(
    'extension_name' => $page['extension_name'],
    'u_extension' => 'extension_view.php?eid='.$page['extension_id'],
    'f_action' => 'extension_authors.php?eid='.$page['extension_id'],
    'users' => $users,
    )
  );

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+
flush_page_messages();
$tpl->assign_var_from_handle('main_content', 'extension_authors');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>