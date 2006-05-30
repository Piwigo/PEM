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

$template->set_file( 'extension_view', 'extension_view.tpl' );
$template->set_block( 'extension_view', 'admin', 't_admin');

$template->set_var(
  array(
    'L_EXTENSION_NAME' => htmlspecialchars ( strip_tags ( $data['name'] ) ),
    'L_EXTENSION_DESCRIPTION' => nl2br(
      htmlspecialchars(
        strip_tags($data['description'])
        )
      ),
    'L_EXTENSION_AUTHOR' => $author,
    'L_EXTENSION_ID' => $id,
    'U_ADD_REV' => 'revision_add.php?eid='.$page['extension_id'],
    'U_MODIFY' => 'extension_mod.php?eid='.$page['extension_id'],
    'U_DELETE' => 'extension_del.php?eid='.$page['extension_id'],
    'U_LINKS' => 'extension_links.php?eid='.$page['extension_id'],
    'U_SHOW_FULL_CL' =>
      'extension_view.php?eid='.$page['extension_id'].'&amp;full_cl=1',
    )
  );
  
if (isAdmin($user['id']) or $user['id'] == $data['idx_user'])
{
  $template->parse( 't_admin', 'admin' );
}

// Links associated to the current extension
$template->set_block('extension_view', 'link', 'Tlink');

$query = '
SELECT name,
       url,
       description
  FROM '.LINKS_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
  ORDER BY rank ASC
;';
$result = $db->query($query);
while ($row = $db->fetch_array($result))
{
  $template->set_var(
    array(
      'LINK_NAME' => $row['name'],
      'LINK_URL' => $row['url'],
      'LINK_DESCRIPTION' => $row['description'],
      )
    );
  
  $template->parse('Tlink', 'link', true);
}

// which revisions to display?
$revision_ids = array();

$query = '
SELECT id_revision
  FROM '.REV_TABLE.' r
    INNER JOIN '.COMP_TABLE.' c ON c.idx_revision = r.id_revision
    INNER JOIN '.EXT_TABLE.' e ON e.id_extension = r.idx_extension
  WHERE id_extension = '.$page['extension_id'];

if (isset($_SESSION['id_version']))
{
  $query.= '
    AND idx_version = '.$_SESSION['id_version'];
}
  
$query.= '
;';
$revision_ids = array_from_query($query, 'id_revision');

$template->set_block( 'extension_view', 'revision', 'Trevision' );
$template->set_block( 'extension_view', 'show_full_cl', 'Tshow_full_cl' );
$template->set_block( 'extension_view', 'hide_full_cl', 'Thide_full_cl' );
$template->set_block( 'extension_view', 'detailed_revision', 'Tdetailed_revision' );
$template->set_block( 'extension_view', 'switch_no_rev', 'Tswitch_no_rev' );

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
  
  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    array_push($revisions, $row);
  }
  
  foreach ($revisions as $revision)
  {
    $template->set_var(
      array(
        'REVISION' => $revision['version'],
        'U_GOTO' => 'revision_view.php?rid='.$revision['id_revision'],
        'VERSIONS_COMPATIBLE' => implode(
          ', ',
          $versions_of[ $revision['id_revision'] ]
          ),
        'DATE' => date('Y-m-d', $revision['date']),
        )
      );
    
    $template->parse( 'Trevision', 'revision', true );
  }
  
  if (isset($_GET['full_cl']))
  {
    $template->set_var(
      array(
        'U_HIDE_FULL_CL' => 'extension_view.php?eid='.$data['id_extension'],
        )
      );
    
    $template->parse( 'Thide_full_cl', 'hide_full_cl');
    
    foreach ($revisions as $revision)
    {
      $template->set_var(
        array(
          'REVISION' => $revision['version'],
          
          'U_GOTO' => 'revision_view.php?rid='.$revision['id_revision'],

          'U_DOWNLOAD' => get_revision_src(
            $page['extension_id'],
            $revision['id_revision'],
            $revision['url']
            ),

          'VERSIONS_COMPATIBLE' => implode(
            ', ',
            $versions_of[ $revision['id_revision'] ]
            ),

          'DATE' => date('Y-m-d', $revision['date']),

          'DESCRIPTION' => nl2br(
            htmlspecialchars($revision['description'])
            ),
          )
        );
      
      $template->parse( 'Tdetailed_revision', 'detailed_revision', true );
    }
  }
  else
  {
    $template->parse('Tshow_full_cl', 'show_full_cl');
  }

  $template->set_var(
    array(
      'EXTENSION_FIRST_REVISION_DATE' => date(
        'Y-m-d',
        $revisions[ count($revisions) - 1 ]['date']
        ),
      
      'EXTENSION_LAST_REVISION_DATE'  => date(
        'Y-m-d',
        $revisions[0]['date']
        ),
      )
    );
}
else
{
  $template->parse( 'Tswitch_no_rev', 'switch_no_rev' );

  $template->set_var(
    array(
      'EXTENSION_FIRST_REVISION_DATE' => l10n('no revision yet'),
      'EXTENSION_LAST_REVISION_DATE'  => l10n('no revision yet'),
      )
    );
}

build_header(); 
$template->parse('output', 'extension_view', true);
build_footer();
?>