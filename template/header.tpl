<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<head>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
  {L_META}
  
  <title>Extensions manager</title>
  <style type="text/css" media="all">@import "template/style.css";</style>
  <link rel="alternate" type="application/rss+xml" href="extensions.rss" title="Extensions" />
  <script type="text/javascript" src="template/functions.js"></script>
</head>

<body>

<h1><a href="index.php">{PAGE_TITLE}</a></h1>

<div id="overall">
  <div id="Menus">
    <div class="menu">
      <ul>
        <!-- BEGIN category -->
        <li><a href="{URL}">{NAME}</a></li>
        <!-- END category -->
    </div>

    <div class="menu">
      <form method="post" action="{L_REQUEST_URI}" style="margin:0;padding:0;">
      Filtre de version<br />
      <select name="pwg_version" style="width:100px;">
        <option value="0">&lt; Aucun &gt;</option>
        <!-- BEGIN pwg_version -->
        <option value="{L_PWG_VERSION_ID}" {L_PWG_VERSION_SELECTED}>{L_PWG_VERSION_NAME}</option>
        <!-- END pwg_version -->
      </select>
      <input type="submit" value="OK" name="compatibility_change" />
      </form>
    </div>
    
    <!-- BEGIN user_not_logged_in -->
    <div class="menu">
      <a href="identification.php">Se connecter</a>
    </div>
    <!-- END user_not_logged_in -->
    
    <!-- BEGIN user_logged_in -->
    <div class="menu">
      <p>Hello {USERNAME}</p>
      <ul>
        <li><a href="identification.php?action=logout">Déconnexion</a></li>
        <li><a href="my.php">Home</a></li>
        <li><a href="extension_add.php">Ajout</a></li>
      </ul>
    </div>
    <!-- END user_logged_in -->
    
  </div>

  <div id="Content">
