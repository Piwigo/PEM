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

// Checks if the user who wants to delete the revision is really its author
$query = '
SELECT idx_user,
       idx_extension
  FROM '. REV_TABLE.'
    INNER JOIN '.EXT_TABLE.' ON idx_extension = id_extension
  WHERE id_revision = '.$page['revision_id'].'
;';
$req = $db->query($query);
$row = $db->fetch_assoc($req);

if (empty($row['idx_user']))
{
  message_die(l10n('Unknown extension'));
}

if ($row['idx_user'] != $user['id'] and !isAdmin($user['id']))
{
  message_die(l10n('Deletion forbidden'));
}

delete_revisions(array($page['revision_id']));

message_success(
  l10n('Revision successfuly deleted'),
  'extension_view.php?eid='.$row['idx_extension']
  );
?>