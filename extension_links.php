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
    'extension_links' => 'extension_links.tpl'
  )
);

// +-----------------------------------------------------------------------+
// |                             Functions                                 |
// +-----------------------------------------------------------------------+

function order_links($extension_id)
{
  $query = '
SELECT id_link
  FROM '.LINKS_TABLE.'
  WHERE idx_extension = '.$extension_id.'
  ORDER by rank ASC
;';
  $sorted_link_ids = array_from_query($query, 'id_link');

  save_order_links($sorted_link_ids);
}

function save_order_links($sorted_link_ids)
{
  $current_rank = 0;

  $datas = array();

  foreach ($sorted_link_ids as $link_id)
  {
    array_push(
      $datas,
      array(
        'id_link' => $link_id,
        'rank' => ++$current_rank,
        )
      );
  }

  mass_updates(
    LINKS_TABLE,
    array(
      'primary' => array('id_link'),
      'update' => array('rank'),
      ),
    $datas
    );
}

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

$authors = get_extension_authors($page['extension_id']);

if (!in_array($user['id'], $authors) and !isAdmin($user['id']))
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
  if (!preg_match('/^https?:/', $_POST['link_url']))
  {
    message_die('Incorrect URL');
  }

  if (empty($_POST['link_name']))
  {
    message_die('Link name must not be empty');
  }

  // find next rank
  $query = '
SELECT MAX(rank) AS current_rank
  FROM '.LINKS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
;';
  list($current_rank) = $db->fetch_array($db->query($query));

  if (empty($current_rank))
  {
    $current_rank = 0;
  }

  $insert = array(
    'name'            => $_POST['link_name'],
    'url'             => $_POST['link_url'],
    'description'     => $_POST['link_description'],
    'rank'            => $current_rank + 1,
    'idx_extension'   => $page['extension_id'],
    );

  if (!empty($_POST['link_language'])) {
    $insert['idx_language'] = $_POST['link_language'];
  }

  mass_inserts(
    LINKS_TABLE,
    array_keys($insert),
    array($insert)
    );
}

if (isset($_POST['submit_order']))
{
  asort($_POST['linkRank'], SORT_NUMERIC);
  save_order_links(array_keys($_POST['linkRank']));
}

if (isset($_GET['delete']) and is_numeric($_GET['delete']))
{
  $query = '
DELETE
  FROM '.LINKS_TABLE.'
  WHERE id_link = '.$_GET['delete'].'
    AND idx_extension = '.$page['extension_id'].'
;';
  $db->query($query);

  order_links($page['extension_id']);
}

// +-----------------------------------------------------------------------+
// |                            Form display                               |
// +-----------------------------------------------------------------------+

$tpl->assign(
  array(
    'u_extension' => 'extension_view.php?eid='.$page['extension_id'],
    'f_action' => 'extension_links.php?eid='.$page['extension_id'],
    'extension_name' => $page['extension_name'],
    )
  );

$tpl_links =array();
  
$query = '
SELECT
    id_link,
    name,
    url,
    description,
    rank
  FROM '.LINKS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
  ORDER BY rank ASC
;';
$result = $db->query($query);
while ($row = $db->fetch_array($result))
{
  $description = '';

  if (!empty($row['description']))
  {
    if (strlen($row['description']) > 50)
    {
      $description = substr($row['description'], 0, 50).'...';
    }
    else
    {
      $description = $row['description'];
    }
  }

  array_push(
    $tpl_links,
    array(
      'id' => $row['id_link'],
      'name' => $row['name'],
      'rank' => $row['rank'] * 10,
      'description' => $description,
      'url' => $row['url'],
      'u_delete' =>
        'extension_links.php?eid='.$page['extension_id'].
        '&amp;delete='.$row['id_link'],
      )
    );
}

$tpl->assign('links', $tpl_links);

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'extension_links');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>