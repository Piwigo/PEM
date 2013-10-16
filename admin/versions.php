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
$root_path = './../';
require_once($root_path . 'include/common.inc.php');
require_once( $root_path . 'include/functions_admin.inc.php' );
require_once( $root_path . 'admin/init.inc.php' );

$tpl->set_filenames(
  array(
    'page' => 'admin/page.tpl',
    'versions' => 'admin/versions.tpl'
  )
);

$tpl->assign('version_form_title', l10n('Add a version'));
$tpl->assign('version_form_type', l10n('add'));

if (isset($_POST['submit_add'])) {
  $insert = array(
    'version' => $_POST['name'],
    );

  mass_inserts(
    VER_TABLE,
    array_keys($insert),
    array($insert)
    );
}

if (isset($_POST['submit_edit'])) {
  mass_updates(
    VER_TABLE,
    array(
      'primary' => array('id_version'),
      'update' => array('version'),
      ),
    array(
      array(
        'id_version' => $_POST['id'],
        'version' => $_POST['name'],
        )
      )
    );

  $tpl->assign('f_action', 'versions.php');
  unset($_GET['edit']);
}

if (isset($_GET['edit'])) {
  $page['version_id'] = abs(intval($_GET['edit']));
  if ($page['version_id'] != $_GET['edit']) {
    message_die('edit URL parameter is incorrect', 'Error', false);
  }

  $tpl->assign('version_form_title', l10n('Modify a version'));
  $tpl->assign('version_form_type', l10n('edit'));
  $tpl->assign('version_form_expanded', true);

  $query = '
SELECT
    id_version,
    version
  FROM '.VER_TABLE.'
  WHERE id_version = '.$page['version_id'].'
;';
  $data = $db->fetch_assoc($db->query($query));
  
  $tpl->assign('version_id', $data['id_version']);
  $tpl->assign('name', $data['version']);
}

if (isset($_GET['delete'])) {
  $page['version_id'] = abs(intval($_GET['delete']));
  if ($page['version_id'] != $_GET['delete']) {
    message_die('edit URL parameter is incorrect', 'Error', false);
  }

  delete_version($page['version_id']);
}

// Categories selection
$query = '
SELECT
    id_version,
    version
  FROM '.VER_TABLE.'
;';
$versions = array_reverse(
  versort(
    array_of_arrays_from_query(
      $query
      )
    )
  );

$tpl_versions = array();

// Displays the versions
foreach ($versions as $version)
{
  array_push(
    $tpl_versions,
    array(
      'id' => $version['id_version'],
      'name' => $version['version'],
      )
    );
}

$tpl->assign('versions', $tpl_versions);
$tpl->assign('f_action', 'versions.php');

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'versions');
$tpl->parse('page');
$tpl->p();
?>
