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

// Hacking attempt
if(!defined('INTERNAL'))
{
  die('No right to do that, sorry. :)');
}

$revision_ids = array();
$revision_infos_of = array();
$extension_ids = array();
$extension_infos_of = array();

// retrieve N last updated extensions, filtered on the user version
$query = '
SELECT
    r.idx_extension,
    r.id_revision,
    r.date AS max_date
  FROM '.REV_TABLE.' r';
if (isset($page['filtered_extension_ids'])) {
  if (count($page['filtered_extension_ids']) > 0) {
    $query.= '
  WHERE idx_extension IN ('.$page['filtered_extension_ids_string'].')';
  }
  else {
    $query.='
  WHERE 0=1';
  }
}
$query.= '
  ORDER BY max_date DESC
;';

$all_revision_ids = array_from_query($query, 'id_revision');
$nb_total = count($all_revision_ids);
$revision_ids = $all_revision_ids;

if (count($revision_ids) == 0)
{
  message_die(
    l10n('No extension match your filter'),
    sprintf(
      l10n('%d last revisions added'),
      $conf['nb_last_revs']
      ),
    false
    );
}

// retrieve revisions information
$revision_infos_of = get_revision_infos_of($revision_ids);
$extension_ids = array_unique(
  array_from_subfield(
    $revision_infos_of,
    'idx_extension'
    )
  );

$extension_infos_of = get_extension_infos_of($extension_ids);

$revisions = array();
foreach ($revision_ids as $revision_id)
{
  $extension_id = $revision_infos_of[$revision_id]['idx_extension'];
  
  array_push(
    $revisions,
    array(
      'id' => $revision_id,
      'extension_id' => $extension_id,
      'extension_name' => $extension_infos_of[$extension_id]['name'],
      'about' => $extension_infos_of[$extension_id]['description'],
      'name' => $revision_infos_of[$revision_id]['version'],
      'description' => nl2br(
        htmlspecialchars(
          strip_tags($revision_infos_of[$revision_id]['description'])
          )
        ),
      'date' => date('Y-m-d', $revision_infos_of[$revision_id]['date']),
      'revision_url' => sprintf(
        'extension_view.php?eid=%u&amp;rid=%u#rev%u',
        $extension_id,
        $revision_id,
        $revision_id
        ),
      )
    );
}
$tpl->assign('revisions', $revisions);

$tpl->assign(
  'nb_total',
  $nb_total
  );

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign('main_content', 'index_view_compact.jtpl');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->display('page.jtpl');
?>