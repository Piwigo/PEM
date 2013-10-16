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

/**
 * inserts multiple lines in a table
 *
 * @param string table_name
 * @param array dbfields
 * @param array inserts
 * @return void
 */
function mass_inserts($table_name, $dbfields, $datas)
{
  global $db;
  
  $query = '
INSERT INTO '.$table_name.'
  ('.implode(',', $dbfields).')
   VALUES';
  foreach ($datas as $insert_id => $insert)
  {
    $query.= '
  ';
    if ($insert_id > 0)
    {
      $query.= ',';
    }
    $query.= '(';
    foreach ($dbfields as $field_id => $dbfield)
    {
      if ($field_id > 0)
      {
        $query.= ',';
      }

      if (!isset($insert[$dbfield]) or $insert[$dbfield] == '')
      {
        $query.= 'NULL';
      }
      else
      {
        $query.= "'".$insert[$dbfield]."'";
      }
    }
    $query.=')';
  }
  $query.= '
;';
  
  $db->query($query);
}

/**
 * updates multiple lines in a table
 *
 * @param string table_name
 * @param array dbfields
 * @param array datas
 * @return void
 */
function mass_updates($tablename, $dbfields, $datas)
{
  global $db;
  
  // depending on the MySQL version, we use the multi table update or N
  // update queries
  $query = 'SELECT VERSION() AS version;';
  list($mysql_version) = mysql_fetch_array($db->query($query));
  if (count($datas) < 10 or version_compare($mysql_version, '4.0.4') < 0)
  {
    // MySQL is prior to version 4.0.4, multi table update feature is not
    // available
    foreach ($datas as $data)
    {
      $query = '
UPDATE '.$tablename.'
  SET ';
      $is_first = true;
      foreach ($dbfields['update'] as $num => $key)
      {
        if (!$is_first)
        {
          $query.= ",\n      ";
        }
        $query.= $key.' = ';
        if (isset($data[$key]) and $data[$key] != '')
        {
          $query.= '\''.$data[$key].'\'';
        }
        else
        {
          $query.= 'NULL';
        }
        $is_first = false;
      }
      $query.= '
  WHERE ';
      foreach ($dbfields['primary'] as $num => $key)
      {
        if ($num > 1)
        {
          $query.= ' AND ';
        }
        $query.= $key.' = \''.$data[$key].'\'';
      }
      $query.= '
;';
      $db->query($query);
    }
  }
  else
  {
    // creation of the temporary table
    $query = '
SHOW FULL COLUMNS FROM '.$tablename.'
;';
    $result = $db->query($query);
    $columns = array();
    $all_fields = array_merge($dbfields['primary'], $dbfields['update']);
    while ($row = mysql_fetch_array($result))
    {
      if (in_array($row['Field'], $all_fields))
      {
        $column = $row['Field'];
        $column.= ' '.$row['Type'];
        if (!isset($row['Null']) or $row['Null'] == '')
        {
          $column.= ' NOT NULL';
        }
        if (isset($row['Default']))
        {
          $column.= " default '".$row['Default']."'";
        }
        if (isset($row['Collation']) and $row['Collation'] != 'NULL')
        {
          $column.= " collate '".$row['Collation']."'";
        }
        array_push($columns, $column);
      }
    }

    $temporary_tablename = $tablename.'_'.micro_seconds();

    $query = '
CREATE TABLE '.$temporary_tablename.'
(
'.implode(",\n", $columns).',
PRIMARY KEY ('.implode(',', $dbfields['primary']).')
)
;';
    $db->query($query);
    mass_inserts($temporary_tablename, $all_fields, $datas);
    // update of images table by joining with temporary table
    $query = '
UPDATE '.$tablename.' AS t1, '.$temporary_tablename.' AS t2
  SET '.
      implode(
        "\n    , ",
        array_map(
          create_function('$s', 'return "t1.$s = t2.$s";'),
          $dbfields['update']
          )
        ).'
  WHERE '.
      implode(
        "\n    AND ",
        array_map(
          create_function('$s', 'return "t1.$s = t2.$s";'),
          $dbfields['primary']
          )
        ).'
;';
    $db->query($query);
    $query = '
DROP TABLE '.$temporary_tablename.'
;';
    $db->query($query);
  }
}

/**
 * creates an array based on a query
 *
 * @param string $query
 * @param string $fieldname
 * @return array
 */
function array_from_query($query, $fieldname)
{
  global $db;
  
  $array = array();

  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    array_push($array, $row[$fieldname]);
  }

  return $array;
}

/**
 * create a complete associative array based on a query
 *
 * @param string $query
 * @return array
 */
function array_of_arrays_from_query($query)
{
  global $db;
  
  $array = array();

  $result = $db->query($query);
  while ($row = $db->fetch_assoc($result))
  {
    array_push($array, $row);
  }

  return $array;
}

/**
 * create a simple associative array based on a query
 *
 * @param string $query
 * @param string $keyname
 * @param string $valuename
 * @return array
 */
function simple_hash_from_query($query, $keyname, $valuename)
{
  global $db;
  
  $array = array();

  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    $array[ $row[$keyname] ] = $row[$valuename];
  }

  return $array;
}

/**
 * create an associative array based on a query
 *
 * @param string $query
 * @param string $keyname
 * @return array
 */
function hash_from_query($query, $keyname)
{
  global $db;
  
  $array = array();

  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    $array[ $row[$keyname] ] = $row;
  }

  return $array;
}

?>