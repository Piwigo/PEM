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

/* this file contains functions for users management */

include_once($root_path . 'include/functions_user_'.$conf['user_manager'].'.inc.php');

/**
 * Performs all required actions for user login
 *
 * @param int user_id
 * @param bool remember_me
 * @return void
 */
function log_user($user_id, $username, $password)
{
  global $conf, $user;

  $conf['set_cookie']($user_id, $conf['pass_convert']($password));
  
  session_set_cookie_params($conf['session_length']);
  //session_start();
  
  $user['id'] = $user_id;
  $user['username'] = $username;
}

/**
 * returns all infos of a specific user or a set of users
 */
function get_user_infos_of($user_ids)
{
  global $db;

  if (count($user_ids) == 0) {
    return array();
  }
  
  $user_infos_of = get_user_basic_infos_of($user_ids);

  $query = '
SELECT *
  FROM '.USER_INFOS_TABLE.'
  WHERE idx_user IN ('.implode(',', $user_ids).')
;';
  $result = $db->query($query);
  while ($row = $db->fetch_array($result))
  {
    $user_infos_of[ $row['idx_user'] ] = array_merge(
      $user_infos_of[ $row['idx_user'] ],
      $row
      );
  }

  return $user_infos_of;
}

/**
 * returns an array of all admin emails
 */
function get_admin_email()
{
  global $conf;
  
  $query = '
SELECT '.$conf['user_fields']['email'].' AS email
  FROM '.USERS_TABLE.'
  WHERE '.$conf['user_fields']['id'].' IN ('.implode(',', $conf['admin_users']).')
;';
  return query2array($query, null, 'email');
}

/**
 * checks if the user is administrator
 */
function isAdmin($user_id)
{
  global $conf;

  return in_array($user_id, $conf['admin_users']);
}

/**
 * checks if the user is translator
 */
function isTranslator($user_id)
{
  global $conf;

  return isset($conf['translator_users'][$user_id]);
}

/**
 * compare usernames of two users
 */
function compare_username($a, $b) {
  return strcmp(strtolower($a["username"]), strtolower($b["username"]));
}

/**
 * returns the username of an user or a set of users
 */
function get_author_name($ids)
{
  global $conf;

  if (is_string($ids))
  {
    $authors = array($ids);
  }
  else
  {
    $authors = $ids;
  }

  $result = array();
  foreach($authors as $author)
  {
    $user_infos_of = get_user_infos_of(array($author));

    if (!empty($conf['user_url_template']))
    {
      $author_string = sprintf(
        $conf['user_url_template'],
        $user_infos_of[$author]['id'],
        $user_infos_of[$author]['username']
        );
    }
    else
    {
      $author_string = $user_infos_of[$author]['username'];
    }
    array_push($result, $author_string);
  }
  if (is_string($ids))
  {
    return $result[0];
  }
  return $result;
}

?>