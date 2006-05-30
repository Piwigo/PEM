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

define('INTERNAL', true);
$root_path = './';
require_once($root_path.'include/common.inc.php');

if (isset($_GET['rid']) and is_numeric($_GET['rid']))
{
  $page['revision_id'] = $_GET['rid'];
}
else
{
  message_die(l10n('Incorrect revision identifier'));
}

$query = '
SELECT id_revision
  FROM '.REV_TABLE.'
  WHERE id_revision = '.$page['revision_id'].'
;';
$result = $db->query($query);

if ($db->num_rows($result) == 0)
{
  message_die(l10n('Unknown revision'));
}

$revision_infos_of = get_revision_infos_of(array($page['revision_id']));
$extension_ids = array_unique(
  array_from_subfield(
    $revision_infos_of,
    'idx_extension'
    )
  );

$extension_infos_of = get_extension_infos_of($extension_ids);
$author_ids = array_unique(
  array_from_subfield(
    $extension_infos_of,
    'idx_user'
    )
  );

$versions_of = get_versions_of_revision(array($page['revision_id']));

$author_infos_of = get_user_infos_of($author_ids);

$extension_id = $revision_infos_of[ $page['revision_id'] ]['idx_extension'];
$author_id = $extension_infos_of[$extension_id]['idx_user'];

$template->set_file('revision_view', 'revision_view.tpl' );

$template->set_var(
    array(
      'AUTHOR' => $author_infos_of[$author_id]['username'],
      
      'EXTENSION_NAME' => $extension_infos_of[$extension_id]['name'],

      'EXTENSION_DESCRIPTION' => nl2br(
        htmlspecialchars(
          strip_tags($extension_infos_of[$extension_id]['description'])
          )
        ),

      'U_EXTENSION' => 'extension_view.php?eid='.$extension_id,

      'U_MODIFY' => 'revision_mod.php?rid='.$page['revision_id'],

      'U_DELETE' => 'revision_del.php?rid='.$page['revision_id'],

      'U_DOWNLOAD' => get_revision_src(
        $extension_id,
        $page['revision_id'],
        $revision_infos_of[ $page['revision_id'] ]['url']
        ),

      'REVISION' => $revision_infos_of[ $page['revision_id'] ]['version'],

      'DATE' => date(
        'Y-m-d',
        $revision_infos_of[ $page['revision_id'] ]['date']
        ),

      'VERSIONS_COMPATIBLE' => implode(
        ', ',
        $versions_of[ $page['revision_id'] ]
        ),

      'REVISION_DESCRIPTION' => nl2br(
        htmlspecialchars(
          strip_tags($revision_infos_of[ $page['revision_id'] ]['description'])
          )
        ),
      )
  );

build_header();
$template->parse('output', 'revision_view', true);
build_footer();
?>
