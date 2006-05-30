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
  
if (!isset($user['id']))
{
  message_die(l10n('You must be connected to reach this page'));
}

$page['extension_id'] =
  (isset($_GET['eid']) and is_numeric($_GET['eid']))
  ? $_GET['eid']
  : '';

if (empty($page['extension_id']))
{
  message_die(l10n('Incorrect extension identifier'));
}

// Checks if the user who wants to delete the extension is really its author
$query = '
SELECT idx_user
  FROM '.EXT_TABLE.'
   WHERE id_extension = '.$page['extension_id'].'
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

// Delete all the revisions for the given extension
$query = '
SELECT id_revision
  FROM '.REV_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
;';
$rev_to_delete = array_from_query($query, 'id_revision');
delete_revisions($rev_to_delete);

// Deletes all the categories relations
$query = '
DELETE
  FROM '.EXT_CAT_TABLE.'
  WHERE idx_extension = '.$page['extension_id'].'
;';
$db->query($query);

// And finally delete the extension
$query = '
DELETE
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
$db->query($query);

message_success(
  l10n('extension successfuly deleted'),
  'index.php'
  );

?>