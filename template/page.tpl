<!DOCTYPE html>
<html>
  <head>
    <meta http-equiv="content-type" content="text/html; charset=utf-8">
    {if isset($meta)}{$meta}{/if}
    <title>{$title}</title>
    <style type="text/css" media="all">@import "template/style.css";</style>
    <link rel="alternate" type="application/rss+xml" href="rss.php" title="Extensions">
    <script type="text/javascript" src="template/functions.js"></script>
    {$specific_header}
  </head>
  
  <body> 
    {$banner}
    
    <div id="overall">
      <div id="Menus">
        <div class="menu">
          {'Categories'|@translate}
          <ul class="categoriesList">
            <li {if $cat_is_home}class="selected"{/if}>
              <a href="index.php?cid=null">{'All'|@translate} <span style="font-size:0.8em;">({$total_extensions})</span></a>
            </li>
          {foreach from=$categories item=category}
            <li {if $category.selected}class="selected"{/if}>
              <a href="index.php?cid={$category.id}">
                {$category.name} <span style="font-size:0.8em;">({$category.count})</span></a>
            </li>
          {/foreach}
          </ul>
          
        {if isset($tags)}
          {'Tags'|@translate}
          <div class="tagsCloud">
          {foreach from=$tags item=tag}
            <a href="{$tag.url}" style="font-size:{$tag.size}em;" {if $tag.selected}class="selected"{/if}>{$tag.name}<i> ({$tag.count})</i></a>
          {/foreach}
          
          {if $more_tags}
            {known_script id="jquery" src="template/jquery.min.js"}
            {html_head}<script type="text/javascript">
            jQuery("document").ready(function() {ldelim}
              jQuery("#showMoreTags").click(function() {ldelim}
                jQuery(this).hide();
                jQuery(".moreTags").show();
                return false;
              });
            });
            </script>{/html_head}
            
            <a href="#" id="showMoreTags" style="display:block;">{'+ more'|@translate}</a>
            {foreach from=$more_tags item=tag}
              <a href="{$tag.url}" style="font-size:{$tag.size}em;display:none;" class="moreTags {if $tag.selected}selected{/if}">{$tag.name}<i> ({$tag.count})</i></a>
            {/foreach}
          {/if}
          </div>
        {/if}
          
          <form method="post" action="{$action}" style="margin:0;padding:0;">

          {'Search'|@translate}<br />
          <input name="search" type="text" value="{if isset($search)}{$search}{/if}"/><br />

          {'Version'|@translate}<br />
          <select name="id_version">
            <option value="0">-------</option>
{foreach from=$menu_versions item=version}
            <option value="{$version.id}" {$version.selected}>{$version.name}</option>
{/foreach}
          </select><br />

          {* <!--{'Author'|@translate}<br />
          <select name="id_user">
            <option value="0">-------</option>
{foreach from=$filter_users item=user}
            <option value="{$user.id}" {$user.selected}>{$user.name}</option>
{/foreach}
          </select>--> *}

          <p class="filter_buttons">
            <input type="submit" value="{'Filter'|@translate}" name="filter_submit" />
            <input type="submit" value="{'Reset'|@translate}" name="filter_reset" />
          </p>
          </form>
        </div>
    
        <div class="menu">
{if isset($has_disclaimer)}
          <a href="disclaimer.php">{'Disclaimer'|@translate}</a><br/>
{/if}

{if !$user_is_logged}
	<form method="post" action="{$action}">
			<ul class="ident">
				<li><a href="register.php">{'Register'|@translate}</a><br /></li>
				<li><a href="identification.php">{'Login'|@translate}</a><br /></li>
			</ul>
			{'Username'|@translate}<br />
			<input type="text" name="username" /><br />
			{'Password'|@translate}<br />
			<input type="password" name="password" /><br />
		<div>
			<p class="filter_buttons">
				<input type="submit" name="quickconnect_submit" value="{'Submit'|@translate}" />
			</p>
		</div>
	</form>
{else}
          <p>{'Hello %s'|@translate|sprintf:$username}</p>
          <ul>
            <li><a href="index.php?action=logout">{'Disconnect'|@translate}</a></li>
            <li><a href="my.php">{'My extensions'|@translate}</a></li>
            <li><a href="extension_add.php">{'Add an extension'|@translate}</a></li>
  {if $user_is_admin}
            <li><a href="admin/index.php">{'Administration'|@translate}</a></li>
  {/if}
          </ul>
{/if}
        </div>        
      </div> <!-- Menus -->
    
      <div id="Content">
        <div id="quickNav"><a href="index.php?filter_reset" title="{'Index'|@translate}"><img class="nav" src="template/images/home.png" alt="{'Index'|@translate}"/></a>
{if !$user_is_logged}
	{if isset($has_help)}
          <a href="help_guest.php" title="{'help'|@translate}"><img class="nav" src="template/images/help.png" alt="{'help'|@translate}"/></a>
	{/if}
{else}
	{if isset($has_help_user)}
          <a href="help_user.php" title="{'help'|@translate}"><img class="nav" src="template/images/help.png" alt="{'help'|@translate}"/></a>
	{/if}
{/if}
		</div>
{if count($languages) > 1}
        <div id="langSelect">
          <select onchange="document.location = this.options[this.selectedIndex].value;">
  {foreach from=$languages item=language}
            <option
              value="{$self_uri}lang={$language.code}"
              {if ($user_language == $language.code)}selected="selected"{/if}
            >
              {$language.name}
            </option>
  {/foreach}
          </select>
        </div>
{/if}
      {$main_content}
      </div>
    </div> <!-- overall -->
    
    <div id="footer">
      <a href="rss.php?lang={$user_language}" title="notification feed">{'news feed'|@translate}</a>
      - {'page generated in %s'|@translate|sprintf:$generation_time}
      - {'powered by'|@translate} {$subversion_revision}
    </div> <!-- footer -->

    {$footer}
  </body>
</html>
