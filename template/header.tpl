<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
<head>
  <meta http-equiv="content-type" content="text/html; charset=iso-8859-1">
  {L_META}
  
  <title>Extensions PhpWebGallery</title>
  <style type="text/css" media="all">@import "template/style.css";</style>
  <link rel="alternate" type="application/rss+xml" href="extensions.rss" title="Extensions" />
  <script type="text/javascript" src="template/functions.js"></script>
</head>

<body>

<div id="pwgHeader">

  <h1>PhpWebGallery Extensions</h1>

  <p class="menu">
   <a href="http://phpwebgallery.net">Home</a>
   <a href="http://demo.phpwebgallery.net">Demo</a>
   <a href="http://phpwebgallery.net/doc">Wiki</a>
   <a href="http://download.gna.org/phpwebgallery">Downloads</a>
   <a href="http://forum.phpwebgallery.net">Forum</a>
   <a href="http://bugs.phpwebgallery.net">Bugs</a>
   <a href="http://phpwebgallery.net/ext">Extensions</a>
   <a href="http://stats.gna.org/download.gna.org/phpwebgallery">Statistics</a>
   <a href="https://gna.org/projects/phpwebgallery">Gna!</a>
  </p>

</div> <!-- pwgHeader -->

<!--
<div id="Header">
  <img src="template/images/kcontrol.png" class="header_image" />
  <p style="margin : 9px 0 0 0;">
    Extensions PhpWebGallery ({L_EXTENSIONS_TOTAL_COUNT})
  </p>
</div>
-->

<div id="overall">
  <div id="Menus">
    <div class="menu">
<!--      <h3 title="Retour à l'accueil"><a href="index.php">Accueil</a></h3> -->
      <!-- BEGIN category -->
      <h3 title="{L_CATEGORY_DESCRIPTION}">{U_CATEGORY}</h3>
      <!-- BEGIN category_sublevel -->
      <ul>
        <!-- BEGIN category_sublevel_item -->
        <li><a href="extensions.php?id={L_CATEGORY_SUBLEVEL_ITEM_ID}" title="{L_CATEGORY_SUBLEVEL_DESCRIPTION}">{L_CATEGORY_SUBLEVEL_ITEM}</a></li>
        <!-- END category_sublevel_item -->
      </ul>
      <!-- END category_sublevel -->
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
      <a href="{PUN_ROOT}login.php">Se connecter</a>
    </div>
    <!-- END user_not_logged_in -->
    
    <!-- BEGIN user_logged_in -->
    <div class="menu">
      <a href="extensions.php?action=add">Ajouter extension</a><br />
      <a href="revisions.php?action=add">Ajouter révision</a><br />
      <a href="contributions.php">Voir ses contributions</a><br />
    </div>
    <!-- END user_logged_in -->
    
  </div>

  <div id="Content">
