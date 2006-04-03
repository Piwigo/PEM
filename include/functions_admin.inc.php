<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2006 PEM Team - http://home.gna.org/pem            |
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

  function build_admin_header( $parse = true )
  {
    global $template;
    
    $template->set_file( 'header', 'admin/header.tpl' );
    
    if( $parse )
      $template->parse( 'output', 'header' );
  }
  
  function build_admin_footer()
  {
    global $template;
    
    $template->set_file( 'footer', 'admin/footer.tpl' );
    $template->parse( 'output', 'footer', true );
    $template->p( 'output' );
    exit();
  }
  
  function admin_message_die( $message, $title = 'Erreur', $go_back = true )
  {
    global $template;
    
    build_admin_header();
    $template->set_file('message', 'admin/message.tpl');
    $template->set_var(array( 'L_MESSAGE_TITLE' => $title,
                              'L_MESSAGE_TEXT' => $message));
    $template->set_block('message', 'switch_redirect', 'Tswitch_redirect');
    $template->set_block('message', 'switch_goback', 'Tswitch_goback'); 
    
    if( $go_back )
    {
      $template->parse('Tswitch_goback', 'switch_goback');
    }
    
    $template->parse('output', 'message', true);
    build_admin_footer();
  }
  
  function admin_message_success($message, $redirect = '', $title = 'Succès', $time_redirect = '5')
  {
    global $template;

    build_admin_header( false );
    $template->set_file( 'message', 'admin/message.tpl' );
    $template->set_var(array( 'L_MESSAGE_TITLE' => $title,
                              'L_MESSAGE_TEXT' => $message,
                              'L_META' => '<meta http-equiv="refresh" content="' . 
                                          $time_redirect . ';' . $redirect . '">'));
    $template->set_block('message', 'switch_redirect', 'Tswitch_redirect'); 
    $template->set_block('message', 'switch_goback', 'Tswitch_goback');     
    if(!empty($redirect))
    {
      $template->set_var(array( 'L_TIME_REDIRECT' => $time_redirect,
                                'U_REDIRECT' => $redirect));
      $template->parse('Tswitch_redirect', 'switch_redirect');
    }
    $template->parse('output', 'header');
    $template->parse('output', 'message', true);
    build_admin_footer();
  }
?>