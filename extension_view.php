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
  
$page['extension_id'] = isset($_GET['eid']) ? abs(intval($_GET['eid'])) : null;
if (!isset($page['extension_id']))
{
  message_die(l10n('eid URL parameter is missing'), 'Error', false );
}

// Gets extension informations
$query = '
SELECT description,
       name,
       idx_user,
       id_extension
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
$data = $db->fetch_assoc($db->query($query));
  
if (!isset($data['id_extension']))
{
  message_die(l10n('Unknown extension'), 'Error', false );
}

$user_infos_of = get_user_infos_of(array($data['idx_user']));
$author = $user_infos_of[ $data['idx_user'] ]['username'];

$page['user_can_modify'] = false;
if (isAdmin($user['id']) or $user['id'] == $data['idx_user'])
{
  $page['user_can_modify'] = true;
}

$versions_of_extension = get_versions_of_extension(
  array($page['extension_id'])
  );

// download statistics
$query = '
SELECT
    idx_revision AS revision_id,
    count(*) AS counter
  FROM '.DOWNLOAD_LOG_TABLE.'
  WHERE idx_revision IN (
    SELECT
        id_revision
      FROM '.REV_TABLE.'
      WHERE idx_extension = '.$page['extension_id'].'
  )
  GROUP BY idx_revision
;';
$result = $db->query($query);
$extension_downloads = 0;
$downloads_of_revision = array();
while ($row = $db->fetch_assoc($result)) {
  $extension_downloads += $row['counter'];
  $downloads_of_revision[ $row['revision_id'] ] = $row['counter'];
}


$tpl->assign(
  array(
    'extension_name' => htmlspecialchars(
      strip_tags($data['name'])
      ),
    'description' => nl2br(
      htmlspecialchars(
        strip_tags($data['description'])
        )
      ),
    'author' => $author,
    'first_date' => l10n('no revision yet'),
    'last_date'  => l10n('no revision yet'),
    'compatible_with' => implode(
        ', ',
        $versions_of_extension[$page['extension_id']]
      ),
    'extension_downloads' => $extension_downloads,
    )
  );

  
if (isset($user['id']))
{
  if ($page['user_can_modify'])
  {
    $tpl->assign(
      array(
        'can_modify' => $page['user_can_modify'],
        'u_modify' => 'extension_mod.php?eid='.$page['extension_id'],
        'u_add_rev' => 'revision_add.php?eid='.$page['extension_id'],
        'u_delete' => 'extension_del.php?eid='.$page['extension_id'],
        'u_links' => 'extension_links.php?eid='.$page['extension_id'],
        'u_screenshot'=> 'extension_screenshot.php?eid='.$page['extension_id'],
        )
      );
  }
}

if ($screenshot_infos = get_extension_screenshot_infos($page['extension_id']))
{
  $tpl->assign(
    'thumbnail',
    array(
      'src' => $screenshot_infos['thumbnail_src'],
      'url' => $screenshot_infos['screenshot_url'],
      )
    );
}

// Links associated to the current extension
$query = '
SELECT name,
       url,
       description
  FROM '.LINKS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
  ORDER BY rank ASC
;';
$result = $db->query($query);

$tpl_links = array();

while ($row = $db->fetch_array($result))
{
  array_push(
    $tpl_links,
    array(
      'name' => $row['name'],
      'url' => $row['url'],
      'description' => $row['description'],
      )
    );
}
$tpl->assign('links', $tpl_links);

// which revisions to display?
$revision_ids = array();

$query = '
SELECT id_revision
  FROM '.REV_TABLE.' r
    INNER JOIN '.COMP_TABLE.' c ON c.idx_revision = r.id_revision
    INNER JOIN '.EXT_TABLE.' e ON e.id_extension = r.idx_extension
  WHERE id_extension = '.$page['extension_id'];

if (isset($_SESSION['filter']['id_version']))
{
  $query.= '
    AND idx_version = '.$_SESSION['filter']['id_version'];
}
  
$query.= '
;';
$revision_ids = array_from_query($query, 'id_revision');

$tpl_revisions = array();

if (count($revision_ids) > 0)
{
  $versions_of = get_versions_of_revision($revision_ids);
  
  $revisions = array();
  
  $query = '
SELECT id_revision,
       version,
       description,
       date,
       url
  FROM '.REV_TABLE.'
  WHERE id_revision IN ('.implode(',', $revision_ids).')
  ORDER by date DESC
;';

  $last_date = '';

  $is_first_revision = true;
  
  $result = $db->query($query);  
  while ($row = $db->fetch_array($result))
  {
    if (!isset($first_date_set))
    {
      $tpl->assign(
        'first_date',
        date(
          'Y-m-d',
          $row['date']
          )
        );
      $first_date_set = true;
    }

    $expanded = false;
    
    if (isset($_GET['rid']))
    {
      if ($row['id_revision'] == $_GET['rid'])
      {
        $expanded = true;
      }
    }
    else if ($is_first_revision)
    {
      $expanded = true;
    }

    $is_first_revision = false;
    
    array_push(
      $tpl_revisions,
      array(
        'id' => $row['id_revision'],
        'version' => $row['version'],
        'versions_compatible' => implode(
          ', ',
          $versions_of[ $row['id_revision'] ]
          ),
        'date' => date('Y-m-d', $row['date']),
        'u_download' => 'download.php?rid='.$row['id_revision'],
        'description' => nl2br(
          htmlspecialchars($row['description'])
          ),
        'can_modify' => $page['user_can_modify'],
        'u_modify' => 'revision_mod.php?rid='.$row['id_revision'],
        'u_delete' => 'revision_del.php?rid='.$row['id_revision'],
        'expanded' => $expanded,
        'downloads' => $downloads_of_revision[$row['id_revision']],
        )
      );

    $last_date = $row['date'];
  }

  $tpl->assign(
    'last_date',
    date(
      'Y-m-d',
      $last_date
      )
    );
  
  $tpl->assign('revisions', $tpl_revisions);
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign('main_content', 'extension_view.jtpl');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->display('page.jtpl');
?>