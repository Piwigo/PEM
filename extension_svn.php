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
    'extension_svn' => 'extension_svn.tpl'
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
SELECT name, svn_url, archive_root_dir, archive_name
  FROM '.EXT_TABLE.'
  WHERE id_extension = '.$page['extension_id'].'
;';
$result = $db->query($query);

if ($db->num_rows($result) == 0)
{
  message_die('Incorrect extension identifier');
}
list($page['extension_name'], $svn_url, $root_dir, $archive_name) = $db->fetch_array($result);

// +-----------------------------------------------------------------------+
// |                           Form submission                             |
// +-----------------------------------------------------------------------+

if (isset($_POST['submit']))
{
  if (empty($svn_url))
  {
    $svn_url = $db->escape($_POST['url']);
    $root_dir = ltrim(strrchr(rtrim($svn_url, '/\\'), '/'), '/\\');
    $archive_name = $root_dir . '_%.zip';
  }
  else
  {
    if (preg_match('/[^a-z0-9_-]/i', $_POST['root_dir']))
    {
      message_die('Characters not allowed in archive root directory.');
    }
    if (preg_match('/[^a-z0-9_\-%\.]/i', $_POST['archive_name']))
    {
      message_die('Characters not allowed in archive name.');
    }

    $svn_url = $db->escape($_POST['url']);
    $root_dir = $db->escape($_POST['root_dir']);
    $archive_name = $db->escape($_POST['archive_name']);

    $extension = substr(strrchr($_POST['archive_name'], '.' ), 1, strlen($_POST['archive_name']));
    if ($extension != 'zip')
    {
      $archive_name .= '.zip';
    }
  }

  $query = '
UPDATE '.EXT_TABLE.'
SET svn_url = "'.$svn_url.'",
    archive_root_dir = "'.$root_dir.'",
    archive_name = "'.$archive_name.'"
WHERE id_extension = '.$page['extension_id'].';';

  $db->query($query);
}

if (isset($_POST['delete']))
{
  unset($svn_url, $root_dir, $archive_name);

  $query = '
UPDATE '.EXT_TABLE.'
SET svn_url = NULL,
    archive_root_dir = NULL,
    archive_name = NULL
WHERE id_extension = '.$page['extension_id'].';';

  $db->query($query);
}

// +-----------------------------------------------------------------------+
// |                            Form display                               |
// +-----------------------------------------------------------------------+

if (!empty($svn_url))
{
  exec($conf['svn_path'].' info '.escapeshellarg($svn_url), $svn_infos);

  if (empty($svn_infos))
  {
    $svn_infos = array(l10n('Unable to retrieve SVN data!'));
  }

  $tpl->assign(
    array(
      'SVN_INFOS' => $svn_infos,
      'ROOT_DIR' => $root_dir,
      'ARCHIVE_NAME' => $archive_name,
    )
  );
}

$tpl->assign(
  array(
    'extension_name' => $page['extension_name'],
    'u_extension' => 'extension_view.php?eid='.$page['extension_id'],
    'f_action' => 'extension_svn.php?eid='.$page['extension_id'],
    'SVN_URL' => (!empty($svn_url) ? $svn_url : ''),
  )
);

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'extension_svn');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>