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
    'reviews' => 'admin/reviews.tpl'
  )
);

if (isset($_GET['delete_review']))
{
  $query = '
DELETE FROM '.REVIEW_TABLE.'
  WHERE id_review = '.$_GET['delete_review'].'
;';
  $db->query($query);
}
else if (isset($_GET['validate_review']))
{
  $query = '
UPDATE '.REVIEW_TABLE.'
  SET validated = "true"
  WHERE id_review = '.$_GET['validate_review'].'
;';
  $db->query($query);
}

$query = '
SELECT *
  FROM '.REVIEW_TABLE.'
  WHERE validated = "false"
  ORDER BY date DESC
;';
$tpl_reviews = array_of_arrays_from_query($query, 'id_review');

if (count($tpl_reviews))
{
  $extensions_ids = array_map(create_function('$v', 'return $v["idx_extension"];'), $tpl_reviews); 
  $extensions_infos_of = get_extension_infos_of($extensions_ids);

  foreach ($tpl_reviews as &$review)
  {
    $review['extension_name'] = $extensions_infos_of[ $review['idx_extension'] ]['name'];
    $review['content'] = nl2br($review['content']);
    $review['date'] = date('d F Y H:i:s', strtotime($review['date']));
    $review['u_delete'] = 'reviews.php?delete_review='.$review['id_review'];
    $review['u_validate'] = 'reviews.php?validate_review='.$review['id_review'];
  }

  $tpl->assign('reviews', $tpl_reviews);
}

$tpl->assign('nb_reviews', count($tpl_reviews));
$tpl->assign('f_action', 'reviews.php');

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'reviews');
$tpl->parse('page');
$tpl->p();
?>
