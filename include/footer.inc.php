<?php
// +-----------------------------------------------------------------------+
// | PEM - a PHP based Extension Manager                                   |
// | Copyright (C) 2005-2006 PEM Team - http://home.gna.org/pem            |
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

$tpl->assign(
  'generation_time',
  (intval(microtime(true) * 1000) - $page['start']).' ms'
  );

ob_start();
include($conf['footer_filepath']);
$footer = ob_get_contents();
ob_end_clean();

$subversion_revision = get_Subversion_revision();
if (isset($subversion_revision)) {
  $tpl->assign(
    'subversion_revision',
    $subversion_revision
    );
}

$tpl->assign('footer', $footer);

?>