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
    'languages' => 'admin/languages.tpl'
  )
);

// +-----------------------------------------------------------------------+
// |                           form process                                |
// +-----------------------------------------------------------------------+

if (isset($_POST['submit']))
{
  if (!isset($_POST['int_languages'][$conf['default_language']]))
  {
    $_POST['int_languages'][] = $conf['default_language'];
  }
  $db->query('UPDATE '.LANG_TABLE.' SET interface = "false", extensions = "false";');

  $db->query('UPDATE '.LANG_TABLE.' SET interface = "true" WHERE code IN ("'.implode('","', $_POST['int_languages']).'");');

  if (!empty($_POST['ext_languages']))
  {
    $db->query('UPDATE '.LANG_TABLE.' SET extensions = "true" WHERE code IN ("'.implode('","', $_POST['ext_languages']).'");');
  }
  message_success('Configuration saved.', 'languages.php');
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

// Get db languages
$query = 'SELECT * FROM '.LANG_TABLE.';';
$result = $db->query($query);
$db_languages = array();
$db_int_languages = array();
$db_ext_languages = array();
while ($row = mysql_fetch_assoc($result))
{
  $db_languages[$row['code']] = $row['id_language'];
  if ($row['interface'] == 'true')
  {
    array_push($db_int_languages, $row['code']);
  }
  if ($row['extensions'] == 'true')
  {
    array_push($db_ext_languages, $row['code']);
  }
}

// Get dir languages
$dir = opendir($root_path.'language');
$languages = array();
$int_languages = array();
$ext_languages = array();

// it's time to refresh language names in the database, sometimes the iso
// code may change in iso.txt
$updates = array();

while ($file = readdir($dir))
{
  $path = $root_path.'language/'.$file;
  if (!is_link($path) and file_exists($path.'/iso.txt'))
  {
    list($language_name) = @file($path.'/iso.txt');
    $language_name = trim($language_name);
    $languages[$file] = $language_name;
    if (file_exists($path.'/common.lang.php'))
    {
      $int_languages[$file] = $language_name;
    }
    $ext_languages[$file] = $language_name;

    if (isset($db_languages[$file])) {
      array_push(
        $updates,
        array(
          'id_language' => $db_languages[$file],
          'name' => $language_name,
          )
        );
    }
  }
}
closedir($dir);
@asort($languages);
@asort($int_languages);
@asort($ext_languages);

if (count($updates) > 0) {
  mass_updates(
    LANG_TABLE,
    array(
      'primary' => array('id_language'),
      'update' => array('name'),
      ),
    $updates
    );
}

// Add new languages to DB
$add = array_diff_key($languages, $db_languages);
if (!empty($add))
{
  foreach ($add as $code => $name)
  {
    $insert[] = '("'.$code.'", "'.$name.'")';
  }
  $query = 'INSERT INTO '.LANG_TABLE.' (`code`, `name`) VALUES '.implode(', ', $insert).';';
  $db->query($query);
}

// unactive missing languages in database
$del = array_diff_key($db_languages, $languages);
if (!empty($del))
{
  $query = '
UPDATE '.LANG_TABLE.'
  SET interface = "false",
      extensions = "false"
  WHERE id_language IN ('.implode(',', $del).')
;';
  $db->query($query);
}

// Display language ids
$query = 'SELECT id_language, code FROM '.LANG_TABLE.';';
$result = $db->query($query);
while ($row = mysql_fetch_assoc($result))
{
  if (isset($int_languages[$row['code']]))
  {
    $int_languages[$row['code']] = ' ('.$row['id_language'].') '. $int_languages[$row['code']];
  }
  if (isset($ext_languages[$row['code']]))
  {
    $ext_languages[$row['code']] = ' ('.$row['id_language'].') '. $ext_languages[$row['code']];
  }
}

$tpl->assign(array(
  'f_action'      => 'languages.php',
  'int_languages' => $int_languages,
  'ext_languages' => $ext_languages,
  'selected_int'  => $db_int_languages,
  'selected_ext'  => $db_ext_languages,
  'default_language' => $conf['default_language'],
  )
);

$tpl->assign_var_from_handle('main_content', 'languages');
$tpl->parse('page');
$tpl->p();
?>
