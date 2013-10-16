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

if (!defined('INTERNAL'))
{
  die('No right to do that, sorry. :)');
}
if (!isset($tpl->files['page']))
{
  $tpl->set_filename('page', 'page.tpl');
}
$tpl->set_filename('message', 'message.tpl');
  
if ($page['message']['is_success'])
{
  if (!isset($page['message']['title']))
  {
    $page['message']['title'] = l10n('Success');
  }

  if (!isset($page['message']['time_redirect']))
  {
    $page['message']['time_redirect'] = $conf['time_redirect'];
  }
  
  if (isset($page['message']['redirect']))
  {
    $tpl->assign(
      array(
        'time_redirect' => $page['message']['time_redirect'],
        'u_redirect' => $page['message']['redirect'],
        'meta' =>
          sprintf(
            '<meta http-equiv="refresh" content="%s;url=%s">',
            $page['message']['time_redirect'],
            $page['message']['redirect']
            ),
        )
      );
  }
  $page['message']['go_back'] = false;
}
else
{
  if (!isset($page['message']['title']))
  {
    $page['message']['title'] = l10n('Error');
  }

  if (!isset($page['message']['go_back']))
  {
    $page['message']['go_back'] = true;
  }
}

$tpl->assign(
  array(
    'message_title' => $page['message']['title'],
    'message_text' => $page['message']['message'],
    'go_back' => $page['message']['go_back'],
    )
  );

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign_var_from_handle('main_content', 'message');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->parse('page');
$tpl->p();
exit();
?>