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

define( 'INTERNAL', true );
$root_path = './';
require_once( $root_path . 'include/common.inc.php' );
  
$page['extension_id'] = isset($_GET['eid']) ? abs(intval($_GET['eid'])) : null;
if (!isset($page['extension_id']))
{
  message_die('eid URL parameter is missing', 'Error', false );
}

$tpl->set_filenames(
  array(
    'page' => 'page.tpl',
    'extension_view' => 'extension_view.tpl'
  )
);

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
  message_die('Unknown extension', 'Error', false );
}

$authors = get_extension_authors($page['extension_id']);

$page['user_can_modify'] = false;
if (isset($user['id']) and (isAdmin($user['id']) or in_array($user['id'], $authors)))
{
  $page['user_can_modify'] = true;
}

$user['extension_owner'] = false;
if (isset($user['id']) and (isAdmin($user['id']) or $user['id'] == $data['idx_user']))
{
  $user['extension_owner'] = true;
}

$versions_of_extension = get_versions_of_extension(
  array($page['extension_id'])
  );

$categories_of_extension = get_categories_of_extension(
  array($page['extension_id'])
  );

// print_array($categories_of_extension);
  
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
        strip_tags(
          get_user_language($data['description'])
          )
        )
      ),
    'authors' => get_author_name($authors),
    'first_date' => l10n('no revision yet'),
    'last_date'  => l10n('no revision yet'),
    'compatible_with' => implode(
        ', ',
        $versions_of_extension[$page['extension_id']]
      ),
    'extension_downloads' => $extension_downloads,
    'extension_categories' => $categories_of_extension[$page['extension_id']],
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
        'u_links' => 'extension_links.php?eid='.$page['extension_id'],
        'u_screenshot'=> 'extension_screenshot.php?eid='.$page['extension_id'],
        )
      );
  }
  if ($user['extension_owner'])
  {
    $tpl->assign(
      array(
        'u_delete' => 'extension_del.php?eid='.$page['extension_id'],
        'u_authors' => 'extension_authors.php?eid='.$page['extension_id']
        )
      );
  }
  if ($conf['allow_svn_file_creation'] and $user['extension_owner'])
  {
    $tpl->assign('u_svn', 'extension_svn.php?eid='.$page['extension_id']);
      
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
$lang_id = $conf['default_language'];
if (isset($_SESSION['language'])) {
  $lang_id = $_SESSION['language'];
}

$query = '
SELECT name,
       url,
       description
  FROM '.LINKS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
    AND (lang IS NULL OR lang = \''.$lang_id.'\')
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
      'description' => get_user_language($row['description']),
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
  $languages_of = get_languages_of_revision($revision_ids);
  
  $revisions = array();
  
  $query = '
SELECT id_revision,
       version,
       description,
       date,
       url,
       author
  FROM '.REV_TABLE.'
  WHERE id_revision IN ('.implode(',', $revision_ids).')
  ORDER by date DESC
;';

  $first_date = '';

  $is_first_revision = true;
  
  $result = $db->query($query);  
  while ($row = $db->fetch_array($result))
  {
    if (!isset($last_date_set))
    {
      $tpl->assign(
        'last_date',
        date(
          'Y-m-d',
          $row['date']
          )
        );
      $last_date_set = true;
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
        'languages' => isset($languages_of[$row['id_revision']]) ?
          $languages_of[$row['id_revision']] : array(),
        'date' => date('Y-m-d', $row['date']),
        'author' => (count($authors) > 1 or $row['author'] != $data['idx_user']) ?
                      get_author_name($row['author']) : '',
        'u_download' => 'download.php?rid='.$row['id_revision'],
        'description' => nl2br(
          htmlspecialchars(
            get_user_language($row['description'])
            )
          ),
        'can_modify' => $page['user_can_modify'],
        'u_modify' => 'revision_mod.php?rid='.$row['id_revision'],
        'u_delete' => 'revision_del.php?rid='.$row['id_revision'],
        'expanded' => $expanded,
        'downloads' => isset($downloads_of_revision[$row['id_revision']]) ? 
                        $downloads_of_revision[$row['id_revision']] : 0,
        )
      );

    $first_date = $row['date'];
  }

  $tpl->assign(
    'first_date',
    date(
      'Y-m-d',
      $first_date
      )
    );
  
  $tpl->assign('revisions', $tpl_revisions);
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'extension_view');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>