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

// the mission of this file is to count the download and to send the file
// content in attachement to the HTTP response.

// +-----------------------------------------------------------------------+
// |                               functions                               |
// +-----------------------------------------------------------------------+

function do_error($code, $str) {
  set_status_header($code);
  echo $str;
  exit();
}

/**
  Sets the http status header (200,401,...)
 */
function set_status_header($code, $text='')
{
  if (empty($text))
  {
    switch ($code)
    {
      case 200: $text='OK';break;
      case 301: $text='Moved permanently';break;
      case 302: $text='Moved temporarily';break;
      case 304: $text='Not modified';break;
      case 400: $text='Bad request';break;
      case 401: $text='Authorization required';break;
      case 403: $text='Forbidden';break;
      case 404: $text='Not found';break;
      case 500: $text='Server error';break;
      case 503: $text='Service unavailable';break;
    }
  }
  
  $protocol = $_SERVER["SERVER_PROTOCOL"];
  if ( ('HTTP/1.1' != $protocol) && ('HTTP/1.0' != $protocol) )
  {
    $protocol = 'HTTP/1.0';
  }
  
  if ( version_compare( phpversion(), '4.3.0', '>=' ) )
  {
    header( "$protocol $code $text", true, $code );
  }
  else
  {
    header( "$protocol $code $text" );
  }
}

// +-----------------------------------------------------------------------+
// |                           Common includes                             |
// +-----------------------------------------------------------------------+

define('INTERNAL', true);
$root_path = './';
require_once($root_path.'include/common.inc.php');

// +-----------------------------------------------------------------------+
// |                             Input checks                              |
// +-----------------------------------------------------------------------+

$page['revision_id'] = null;

if (isset($_GET['rid'])) {
  if (is_numeric($_GET['rid'])) {
    $page['revision_id'] = abs(intval($_GET['rid']));
  }
  else {
    do_error(400, 'Invalid request, revision id must be numeric');
  }
}
elseif (isset($_GET['eid'])) {
  if (!is_numeric($_GET['eid'])) {
    do_error(400, 'Invalid request, extension id must be numeric');
  }

  if (isset($_GET['version'])) {
    $version_id_of = array_flip(get_version_name_of());
    if (isset($version_id_of[ $_GET['version'] ])) {
      $version = $version_id_of[ $_GET['version'] ];
    }
    else {
      do_error(400, 'Invalid request, this version does not exist');
    }

    $get_rid_query = '
SELECT
    MAX(id_revision)
  FROM '.REV_TABLE.'
    JOIN '.COMP_TABLE.' c ON c.idx_revision = id_revision
  WHERE idx_extension = '.$_GET['eid'].'
    AND idx_version = '.$version.'
;';
  }
  else {
    // we provide the most recent revision of the extension, that is
    // compatible the given version
    $get_rid_query = '
SELECT
    MAX(id_revision)
  FROM '.REV_TABLE.'
  WHERE idx_extension = '.$_GET['eid'].'
;';
  }

  list($page['revision_id']) = $db->fetch_row($db->query($get_rid_query));
}
else {
  do_error(400, 'Invalid request, missing revision id');
}

if (empty($page['revision_id'])) {
  do_error(400, 'Invalid request, no revision matches your request');
}
$revision_infos_of = get_revision_infos_of(array($page['revision_id']));

if (count($revision_infos_of) == 0)
{
  do_error(404, 'Requested revision id not found');
}

// +-----------------------------------------------------------------------+
// |                                 Log                                   |
// +-----------------------------------------------------------------------+

log_download($page['revision_id']);

// +-----------------------------------------------------------------------+
// |                         HTTP response headers                         |
// +-----------------------------------------------------------------------+

$revision_infos = $revision_infos_of[ $page['revision_id'] ];

$file = get_revision_src(
  $revision_infos['idx_extension'],
  $page['revision_id'],
  $revision_infos['url']
  );

if (!@is_readable($file)) {
  do_error(404, "Requested file not readable - $file");
}

$gmt_mtime = gmdate('D, d M Y H:i:s', filemtime($file)).' GMT';

$content_types = array(
  'zip' => 'application/zip',
  'jar' => 'application/java-archive',
);

$http_headers = array(
  'Content-Length: '.@filesize($file),
  'Last-Modified: '.$gmt_mtime,
  'Content-Type: '.$content_types[ pathinfo($file, PATHINFO_EXTENSION) ],
  'Content-Disposition: attachment; filename="'.basename($file).'";',
  'Content-Transfer-Encoding: binary',
  );

foreach ($http_headers as $header) {
  header($header);
}

// +-----------------------------------------------------------------------+
// |                   HTTP response content : raw file                    |
// +-----------------------------------------------------------------------+

@readfile($file);
?>
