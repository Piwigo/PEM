<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2009 PEM Team - http://home.gna.org/pem            |
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
    'index' => 'admin/index.tpl'
  )
);

// Select the revisions count
$sql =  '
SELECT
    COUNT(id_revision) AS revisions_count
  FROM '.REV_TABLE.'
;';
$req = $db->query($sql);
$data = $db->fetch_assoc($req);
  
$tpl->assign('revisions_count', $data['revisions_count']);

// Are there extension without a single revision?
$query = '
SELECT COUNT(*)
  FROM '.EXT_TABLE.'
    LEFT JOIN '.REV_TABLE.' ON idx_extension = id_extension
  WHERE id_revision IS NULL
;';
list($count) = $db->fetch_row($db->query($query));
if ($count > 0) {
  $tpl->assign(
    array(
      'empty_extensions_count' => $count,
      'empty_extensions_url' => 'empty_extensions.php',
      )
    );
}

// Are there revisions compatible to no version?
$query = '
SELECT
    name,
    id_revision,
    idx_extension,
    nb_downloads,
    version,
    idx_version
  FROM '.REV_TABLE.'
    JOIN '.EXT_TABLE.' ON idx_extension = id_extension
    LEFT JOIN '.COMP_TABLE.' ON id_revision = idx_revision
  WHERE idx_version IS NULL
;';
$tpl->assign('no_compat_revs', array_of_arrays_from_query($query));

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'index');
$tpl->pparse('page');
?>
