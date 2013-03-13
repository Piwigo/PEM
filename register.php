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

if (isset($conf['external_register_url']))
{
  header('Location: '.$conf['external_register_url']);
  exit();
}

$tpl->set_filenames(
  array(
    'page' => 'page.tpl',
    'register' => 'register.tpl'
  )
);

if (isset($conf['recaptcha']['activation']) and $conf['recaptcha']['activation'])
{
  require_once($conf['recaptcha']['file_path']);
  /*with the code bellow the translation is not needed for these js vars :
   instructions_visual : "{'instructions_visual'|@translate}",
   instructions_audio : "{'instructions_audio'|@translate}",
   play_again : "{'play_again'|@translate}",
   cant_hear_this : "{'cant_hear_this'|@translate}",
   visual_challenge : "{'visual_challenge'|@translate}",
   audio_challenge : "{'audio_challenge'|@translate}",
   refresh_btn : "{'refresh_btn'|@translate}",
   help_btn : "{'help_btn'|@translate}",
   incorrect_try_again : "{'incorrect_try_again'|@translate}",
   the languages 'en','nl','fr','de','pt','ru','es','tr' will be automaticly traduced. For the other languages, tall the js var must be traduced or none of them, because of the if() line 55
  */
  $code_lang = array();
  $code_lang = explode('_', $_SESSION['language']['code']);
  
  if (array_search($code_lang[0], $conf['recaptcha']['lang']))
  {
    $recaptcha_lang=$code_lang[0];
  }
  else
  {
    $recaptcha_lang='en';
    if (l10n('instructions_visual') != 'instructions_visual')
    {
      $tpl->assign(
    	array(
          'custom_translation' => true,
          )
        );
    }
  }
  
  $tpl->assign(
    array(
      'html_recaptcha' => recaptcha_get_html($conf['recaptcha']['publickey']),
      'lang' => $recaptcha_lang,
      'theme' => $conf['recaptcha']['theme'],
      )
    );
}

if (isset($_POST['submit']))
{
  $errors = array();
  
  if ($_POST['password'] != $_POST['confirmation_password'])
  {
    array_push(
      $errors,
      l10n('Confirmation password does not match.')
      );
  }

  if (isset($conf['recaptcha']['activation']) and $conf['recaptcha']['activation'])
  {
    $resp = recaptcha_check_answer(
      $conf['recaptcha']['privatekey'],
      $_SERVER['REMOTE_ADDR'],
      $_POST['recaptcha_challenge_field'],
      $_POST['recaptcha_response_field']
      );
    
    if (!$resp->is_valid) {
      array_push(
        $errors,
        l10n('CAPTCHA_error')
        );
    }
  }

  if (count($errors) == 0)
  {
    $register_errors = register_user(
      $_POST['username'],
      $_POST['password'],
      $_POST['email']
      );
    
    $errors = array_merge($errors, $register_errors);
  }

  if (count($errors) == 0)
  {
    $user_id = get_userid($_POST['username']);
    log_user($user_id, $_POST['username'], $_POST['password']);
    message_success('Registration successful', 'index.php');
  }
  else
  {
    message_die($errors[0]);
  }
}

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'register');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
?>
