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
$upgrade_infos = array();

require_once($root_path . 'include/config_default.inc.php');
@include($root_path . 'include/config_local.inc.php');
require_once($root_path . 'include/constants.inc.php');
require_once($root_path . 'include/dblayer/common_db.php');

// +-----------------------------------------------------------------------+
// |                         Languages management                          |
// +-----------------------------------------------------------------------+

$query = 'SHOW TABLES LIKE "'.LANG_TABLE.'";';
$result = $db->query($query);
if (!mysql_fetch_row($result))
{
  $query = '
CREATE TABLE  `'.LANG_TABLE.'` (
  `id_language` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(5) NOT NULL,
  `name` varchar(50) NOT NULL,
  `interface` enum(\'true\',\'false\') NOT NULL default \'false\',
  `extensions` enum(\'true\',\'false\') NOT NULL default \'false\',
  PRIMARY KEY (`id_language`),
  KEY `languages_i2` (`interface`),
  KEY `languages_i3` (`extensions`)
)  DEFAULT CHARSET=utf8;';
  $db->query($query);

  // Get dir languages
  $dir = opendir($root_path.'language');
  $languages = array();

  while ($file = readdir($dir))
  {
    $path = $root_path.'language/'.$file;
    if (!is_link($path) and file_exists($path.'/iso.txt'))
    {
      list($language_name) = @file($path.'/iso.txt');
      $languages[$file] = $language_name;
    }
  }
  closedir($dir);
  @asort($languages);
  array_push($upgrade_infos, 'Languages table has been created');

  // Add new languages to DB
  if (!empty($languages))
  {
    foreach ($languages as $code => $name)
    {
      $insert[] = '("'.$code.'", "'.$name.'")';
    }
    $query = 'INSERT INTO '.LANG_TABLE.' (`code`, `name`) VALUES '.implode(', ', $insert).';';
    $db->query($query);

    $query = 'UPDATE '.LANG_TABLE.' SET interface = "true" where code = "'.$conf['default_language'].'";';
    $db->query($query);
  }
}

// +-----------------------------------------------------------------------+
// |                           Check access                                |
// +-----------------------------------------------------------------------+
require_once($root_path.'include/common.inc.php');

if (empty($user['id']) or !isAdmin($user['id']))
{
  die('You must be connected as administrator...');
}

$languages = get_languages_from_table();

// +-----------------------------------------------------------------------+
// |                              Functions                                |
// +-----------------------------------------------------------------------+

function get_languages_from_table()
{
  global $db;

  $query = 'SELECT id_language, code FROM '.LANG_TABLE.';';
  $result = $db->query($query);
  $languages = array();
  while ($row = mysql_fetch_assoc($result))
  {
    $code = substr($row['code'], 0, 2);
    $languages[$code] = $row['id_language'];
  }
  return $languages;
}

function get_converted_translations($type, $table, $trans_table)
{
  global $db, $conf;

  $languages = get_languages_from_table();
  $default_language = substr($conf['default_language'], 0, 2);

  $query = 'SELECT id_'.$type.', description FROM '.$table.';';
  $result = $db->query($query);
  $translations = array();

  while ($row = mysql_fetch_assoc($result))
  {
    $id = $row['id_'.$type];
    $desc = $row['description'];

    $pattern = '#\[lang=(.*?)\](.*?)\[/lang\]#is';
    preg_match_all($pattern, $desc, $matches, PREG_SET_ORDER);

    if (!empty($matches))
    {
      $translations[$id] = array();
      $all = '';
      foreach ($matches as $match)
      {
        if ($match[1] == 'default')
        {
          $translations[$id]['default'] = $all . $match[2];
        }
        elseif ($match[1] == 'all')
        {
          $all .= $match[2];
          foreach ($translations[$id] as $k => $translation)
          {
            $translations[$id][$k] .= $match[2];
          }
        }
        elseif (isset($languages[$match[1]]))
        {
          $translations[$id][$languages[$match[1]]] = $all . $match[2];
        }
      }
    }
    else
    {
      continue;
    }

    if (!isset($translations[$id]['default']))
    {
      if (isset($translations[$id][$languages[$default_language]]))
      {
        $translations[$id]['default'] = $translations[$id][$languages[$default_language]];
        unset($translations[$id][$languages[$default_language]]);
      }
      else
      {
        $patterns[] = '#\[lang=all\](.*?)\[/lang\]#is';
        $replacements[] = '\\1';
        $patterns[] = '#\[lang=.*?\].*?\[/lang\]#is';
        $replacements[] = '';
        $translations[$id]['default'] = preg_replace($patterns, $replacements, $desc);
      }
    }
  }

  // Write translations into database
  $i = array(0, 0);
  foreach ($translations as $id => $translation)
  {
    foreach ($translation as $id_lang => $desc)
    {
      if ($id_lang == 'default')
      {
        $query = '
UPDATE '.$table.'
  SET description = "'.addslashes(trim($desc)).'"
  WHERE id_'.$type.' = '.$id.'
;';
        $db->query($query);
        $i[0]++;
      }
      else
      {
        $query = '
INSERT INTO '.$trans_table.' (`idx_'.$type.'`, `idx_language`, `description`)
  VALUES ('.$id.', '.$id_lang.', "'.addslashes(trim($desc)).'")
;';
        $db->query($query);
      }
      $i[1]++;
    }
  }
  return $i;
}

// +-----------------------------------------------------------------------+
// |                  Upgrade column lang in links table                   |
// +-----------------------------------------------------------------------+

$query = 'SHOW FULL COLUMNS FROM ' . LINKS_TABLE . ';';
$fields = (array_from_query($query, 'Field'));

if (in_array('lang', $fields))
{
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
  array_push($upgrade_infos, '- '.$i.' row(s) updated in links table');
}

// +-----------------------------------------------------------------------+
// |                       Add translation table                           |
// +-----------------------------------------------------------------------+

$query = 'SHOW TABLES LIKE "'.EXT_TRANS_TABLE.'";';
$result = $db->query($query);

if (!mysql_fetch_row($result))
{
  // Add column idx_default_language
  $query = 'ALTER TABLE '.EXT_TABLE.' ADD `idx_language` INT( 11 ) NOT NULL AFTER `description` ';
  $db->query($query);
  $query = 'UPDATE '.EXT_TABLE.' SET idx_language = '.$languages[substr($conf['default_language'], 0, 2)].';';
  $db->query($query);

  // Create translation table
  $query = '
CREATE TABLE `'.EXT_TRANS_TABLE.'` (
  `idx_extension` int(11) NOT NULL,
  `idx_language` int(11) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY  (`idx_extension`, `idx_language`)
) DEFAULT CHARSET=utf8
;';
  $db->query($query);
  array_push($upgrade_infos, 'Extensions translations table has been created');

  // Get descriptions and find translations
  $i = get_converted_translations('extension', EXT_TABLE, EXT_TRANS_TABLE);
  array_push($upgrade_infos, '- '.$i[0].' row(s) updated in extensions table');
  array_push($upgrade_infos, '- '.$i[1].' row(s) inserted in extensions translations table');
}

$query = 'SHOW TABLES LIKE "'.REV_TRANS_TABLE.'";';
$result = $db->query($query);
if (!mysql_fetch_row($result))
{
  // Add column idx_default_language
  $query = 'ALTER TABLE '.REV_TABLE.' ADD `idx_language` INT( 11 ) NOT NULL AFTER `description` ';
  $db->query($query);
  $query = 'UPDATE '.REV_TABLE.' SET idx_language = '.$languages[substr($conf['default_language'], 0, 2)].';';
  $db->query($query);

  // Create translation table
  $query = '
CREATE TABLE `'.REV_TRANS_TABLE.'` (
  `idx_revision` int(11) NOT NULL,
  `idx_language` int(11) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY  (`idx_revision`, `idx_language`)
) DEFAULT CHARSET=utf8
;';
  $db->query($query);
  array_push($upgrade_infos, 'Revisions translations table has been created');

  // Get descriptions and find translations
  $i = get_converted_translations('revision', REV_TABLE, REV_TRANS_TABLE);
  array_push($upgrade_infos, '- '.$i[0].' row(s) updated in revisions table');
  array_push($upgrade_infos, '- '.$i[1].' row(s) inserted in revisions translations table');
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