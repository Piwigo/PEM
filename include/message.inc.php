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

if (!defined('INTERNAL'))
{
  die('No right to do that, sorry. :)');
}
  
if ($page['message']['is_success'])
{
  if (!isset($page['message']['title']))
  {
    $page['message']['title'] = 'Success';
  }

  if (!isset($page['message']['time_redirect']))
  {
    $page['message']['time_redirect'] = 5;
  }
  
  if (isset($page['message']['redirect']))
  {
    $tpl->assign(
      array(
        'time_redirect' => $page['message']['time_redirect'],
        'u_redirect' => $page['message']['redirect'],
        'meta' =>
          sprintf(
            '<meta http-equiv="refresh" content="%s;%s">',
            $page['message']['time_redirect'],
            $page['message']['redirect']
            ),
        )
      );
  }
}
else
{
  if (!isset($page['message']['title']))
  {
    $page['message']['title'] = 'Error';
  }

  if (!isset($page['message']['go_back']))
  {
    $page['message']['go_back'] = true;
  }

  $tpl->assign('go_back', $page['message']['go_back']);
}

$tpl->assign(
  array(
    'message_title' => $page['message']['title'],
    'message_text' => $page['message']['message'],
    )
  );

// +-----------------------------------------------------------------------+
// |                           html code display                           |
// +-----------------------------------------------------------------------+

$tpl->assign('main_content', 'message.jtpl');
include($root_path.'include/header.inc.php');
include($root_path.'include/footer.inc.php');
$tpl->display('page.jtpl');
exit();
?>