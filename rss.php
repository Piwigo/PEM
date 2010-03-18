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
$root_path = './';
require_once($root_path.'include/common.inc.php');

$tpl->set_filename('rss', 'rss.tpl');

// $conf['page_title']
$tpl->assign(
  array(
    'xml_header' => '<?xml version="1.0" encoding="utf-8"?>',
    'title' => $conf['page_title'],
    'website_url' => $conf['website_url'],
    'description' => $conf['website_description'],
    'language' => $conf['website_language'],
    'webmaster_email' => $conf['webmaster_email'],
    )
  );

// Gets the latest X (defined in constants.inc.php) mods information
$query = '
SELECT
    version,
    description,
    id_revision,
    idx_extension
  FROM '.REV_TABLE.'
  ORDER BY id_revision DESC
  LIMIT 0, '.$conf['rss_nb_items'].'
;';
$req = $db->query($query);

$revisions = array();
$extension_ids = array();
$user_ids = array();

while ($data = $db->fetch_assoc($req))
{
  array_push($revisions, $data);
  array_push($extension_ids, $data['idx_extension']);
}

$extension_infos_of = get_extension_infos_of($extension_ids);

foreach ($extension_ids as $extension_id)
{
  array_push($user_ids, $extension_infos_of[$extension_id]['idx_user']);
}
$author_infos_of = get_user_basic_infos_of($user_ids);

$tpl_revisions = array();
foreach ($revisions as $revision)
{
  $extension = $extension_infos_of[ $revision['idx_extension'] ];
  $author = $author_infos_of[ $extension['idx_user'] ];

  array_push(
    $tpl_revisions,
    array(
      'ext_name' => $extension['name'],
      'name' => $revision['version'],
      'url' => sprintf(
        '%s/extension_view.php?eid=%u&amp;rid=%u#rev%u',
        $conf['website_url'],
        $revision['idx_extension'],
        $revision['id_revision'],
        $revision['id_revision']
        ),
      'ext_author' => $author['username'],
      'ext_description' => $extension['description'],
      'description' => $revision['description'],
      )
    );
}
$tpl->assign('revisions', $tpl_revisions);

// echo '<pre>'; print_r($tpl_revisions); echo '</pre>';

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->pparse('rss');
?>
