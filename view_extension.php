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
  
$id = isset($_GET['id']) ? abs(intval($_GET['id'])) : '';
  
// Gets extension informations
$query = '
SELECT description,
       username,
       name,
       idx_author,
       id_extension
  FROM '.EXT_TABLE.'
    INNER JOIN '.USERS_TABLE.' ON id = idx_author
  WHERE id_extension = '.$id.'
;';
$data = $db->fetch_assoc($db->query($query));
  
if (!isset($data['id_extension']))
{
  message_die( 'L\'extension désirée n\'existe pas.', 'Erreur', false );
}

$template->set_file( 'view_extension', 'view_extension.tpl' );
$template->set_block( 'view_extension', 'admin', 't_admin');

$template->set_var(
  array(
    'L_EXTENSION_NAME' => htmlspecialchars ( strip_tags ( $data['name'] ) ),
    'L_EXTENSION_DESCRIPTION' => nl2br( htmlspecialchars ( strip_tags ( $data['description'] ) ) ),
    'L_EXTENSION_AUTHOR' => htmlspecialchars( $data['username'] ),
    'L_EXTENSION_ID' => $id,
    'U_ADD_REV' => 'revisions.php?extension_id='.$data['id_extension'],
    'U_MODIFY' => 'toto.php',
    'U_SHOW_FULL_CL' => 'view_extension.php?id='.$data['id_extension'].'&amp;full_cl=1',
    )
  );

if (isAdmin($user['id']) or $user['id'] == $data['idx_author'])
{
  $template->parse( 't_admin', 'admin' );
}

// which revisions to display?
$revision_ids = array();

$query = '
SELECT id_revision
  FROM '.REV_TABLE.' r
    INNER JOIN '.COMP_TABLE.' c ON c.idx_revision = r.id_revision
    INNER JOIN '.EXT_TABLE.' e ON e.id_extension = r.idx_extension
  WHERE id_extension = '.$id;

if (isset($_SESSION['id_version']))
{
  $query.= '
    AND idx_version = '.$_SESSION['id_version'];
}

$query.= '
;';

$result = $db->query($query);

while ($row = $db->fetch_array($result))
{
  array_push($revision_ids, $row['id_revision']);
}

$template->set_block( 'view_extension', 'revision', 'Trevision' );
$template->set_block( 'view_extension', 'show_full_cl', 'Tshow_full_cl' );
$template->set_block( 'view_extension', 'hide_full_cl', 'Thide_full_cl' );
$template->set_block( 'view_extension', 'detailed_revision', 'Tdetailed_revision' );
$template->set_block( 'view_extension', 'switch_no_rev', 'Tswitch_no_rev' );

if (count($revision_ids) > 0)
{
  // Get list of compatibilities
  $versions_of = array();

  $query = '
SELECT idx_revision,
       v.version
  FROM '.COMP_TABLE.' c
    INNER JOIN '.VER_TABLE.' v  ON v.id_version = c.idx_version
  WHERE idx_revision IN ('.implode(',', $revision_ids).')
;';

  $result = $db->query($query);

  while ($row = $db->fetch_array($result))
  {
    if (!isset($versions_of[ $row['idx_revision'] ]))
    {
      $versions_of[ $row['idx_revision'] ] = array();
    }

    array_push(
      $versions_of[ $row['idx_revision'] ],
      $row['version']
      );
  }

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
        'U_GOTO' => 'extension.php?id='.$revision['id_revision'],
        'VERSIONS_COMPATIBLE' => implode(', ', $versions_of[ $revision['id_revision'] ]),
        'DATE' => date('Y-m-d', $revision['date']),
        )
      );
    
    $template->parse( 'Trevision', 'revision', true );
  }

  if (isset($_GET['full_cl']))
  {
    $template->set_var(
      array(
        'U_HIDE_FULL_CL' => 'view_extension.php?id='.$data['id_extension'],
        )
      );

    $template->parse( 'Thide_full_cl', 'hide_full_cl');

    foreach ($revisions as $revision)
    {
      $template->set_var(
        array(
          'REVISION' => $revision['version'],
          'U_GOTO' => 'extension.php?id='.$revision['id_revision'],
          'U_DOWNLOAD' => EXTENSIONS_DIR . $revision['url'],
          'VERSIONS_COMPATIBLE' => implode(', ', $versions_of[ $revision['id_revision'] ]),
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
}
else
{
  $template->parse( 'Tswitch_no_rev', 'switch_no_rev' );
}


build_header(); 
$template->parse('output', 'view_extension', true);
build_footer();
?>