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

define('INTERNAL', true);
$root_path = '../';
require_once($root_path.'include/common.inc.php');

if (empty($user['id']) or !isAdmin($user['id']))
{
  die('You must be connected as administrator...');
}

$upgrade_infos = array();

// +-----------------------------------------------------------------------+
// |                  Upgrade column lang in links table                   |
// +-----------------------------------------------------------------------+

$query = 'SHOW FULL COLUMNS FROM ' . LINKS_TABLE . ';';
$fields = array_flip(array_from_query($query, 'Field'));
$types = array_from_query($query, 'Type');

if ($types[$fields['lang']] != 'int(11)')
{
  // Get all languages from language table
  $query = 'SELECT id_language, code FROM '.LANG_TABLE.';';
  $result = $db->query($query);
  $languages = array();
  while ($row = mysql_fetch_assoc($result))
  {
    $code = substr($row['code'], 0, 2);
    $languages[$code] = $row['id_language'];
  }

  // Retrieve language code of links
  $query = '
SELECT id_link,
       lang
  FROM '.LINKS_TABLE.'
  WHERE lang IS NOT NULL
;';
  $result = $db->query($query);
  $links_lang = array();
  while ($row = mysql_fetch_assoc($result))
  {
    $links_lang[$row['id_link']] = $row['lang'];
  }

  // Clear column
  $query = 'UPDATE '.LINKS_TABLE.' SET lang = NULL;';
  $db->query($query);

  // Change column lang to idx_language
  $query = 'ALTER TABLE '.LINKS_TABLE.' CHANGE `lang` `idx_language` INT( 11 ) NULL DEFAULT NULL';
  $db->query($query);

  // Update rows with correct language id
  $i = 0;
  foreach ($links_lang as $id_link => $lang)
  {
    if (isset($languages[$lang]))
    {
      $query = '
UPDATE '.LINKS_TABLE.'
  SET idx_language = '.$languages[$lang].'
  WHERE id_link = '.$id_link.'
  LIMIT 1
;';
    $db->query($query);
    $i++;
    }
  }
  array_push($upgrade_infos, $i.' row(s) updated in links table');
}


// +-----------------------------------------------------------------------+
// |                       Display upgrade result                          |
// +-----------------------------------------------------------------------+

if (empty($upgrade_infos))
{
  echo 'Nothing to upgrade!';
}
else
{
  echo implode("<br>\n", $upgrade_infos);
}

?>