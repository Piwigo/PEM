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

function delete_category($category_id) {
  global $db;
  
  $query = '
DELETE
  FROM '.EXT_CAT_TABLE.'
  WHERE idx_category = '.$category_id.'
;';
  $db->query($query);

  $query = '
DELETE
  FROM '.CAT_TABLE.'
  WHERE id_category = '.$category_id.'
;';
  $db->query($query);
}

function delete_version($version_id) {
  global $db;

  $query = '
DELETE
  FROM '.COMP_TABLE.'
  WHERE idx_version = '.$version_id.'
;';
  $db->query($query);

  $query = '
DELETE
  FROM '.VER_TABLE.'
  WHERE id_version = '.$version_id.'
;';
  $db->query($query);
}
?>
