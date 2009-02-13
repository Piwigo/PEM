<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
        "http://www.w3.org/TR/html4/loose.dtd">
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
          <form method="post" action="{$action}" style="margin:0;padding:0;">
          {'Category'|translate}<br />
          <select name="category_ids[]" multiple="true" size="5">
{foreach from=$categories item=category}
            <option value="{$category.id}" {$category.selected}>{$category.name}</option>
{/foreach}
          </select><br />

          {'Search'|translate}<br />
          <input name="search" type="text" value="{if isset($search)}{$search}{/if}"/><br />

          {'Version'|translate}<br />
          <select name="id_version">
            <option value="0">-------</option>
{foreach from=$menu_versions item=version}
            <option value="{$version.id}" {$version.selected}>{$version.name}</option>
{/foreach}
          </select><br />

          {'Author'|translate}<br />
          <select name="id_user">
            <option value="0">-------</option>
{foreach from=$filter_users item=user}
            <option value="{$user.id}" {$user.selected}>{$user.name}</option>
{/foreach}
          </select>

          <p class="filter_buttons">
            <input type="submit" value="{'Filter'|translate}" name="filter_submit" />
            <input type="submit" value="{'Reset'|translate}" name="filter_reset" />
          </p>
          </form>
        </div>
    
        <div class="menu">
{if isset($has_disclaimer)}
          <a href="disclaimer.php">{'Disclaimer'|translate}</a><br/>
{/if}

{if !$user_is_logged}
	<form method="post" action="{$action}">
			<ul class="ident">
				<li><a href="register.php">{'Register'|translate}</a><br /></li>
				<li><a href="identification.php">{'Login'|translate}</a><br /></li>
			</ul>
			{'Username'|translate}<br />
			<input type="text" name="username" /><br />
			{'Password'|translate}<br />
			<input type="password" name="password" /><br />
		<div>
			<p class="filter_buttons">
				<input type="submit" name="quickconnect_submit" value="{'Submit'|translate}" />
			</p>
		</div>
	</form>
{else}
          <p>{'Hello %s'|translate|sprintf:$username}</p>
          <ul>
            <li><a href="identification.php?action=logout">{'Disconnect'|translate}</a></li>
            <li><a href="my.php">{'Home'|translate}</a></li>
            <li><a href="extension_add.php">{'Add an extension'|translate}</a></li>
  {if $user_is_admin}
            <li><a href="admin/index.php">{'Administration'|translate}</a></li>
  {/if}
          </ul>
{/if}
        </div>        
      </div> <!-- Menus -->
    
      <div id="Content">
        <div id="quickNav"><a href="index.php"><img class="nav" src="template/images/home.png" alt="{'Index'|translate}"/></a>
{if !$user_is_logged}
	{if isset($has_help)}
          <a href="help_guest.php"><img class="nav" src="template/images/help.png" alt="{'help'|translate}"/></a>
	{/if}
{else}
	{if isset($has_help_user)}
          <a href="help_user.php"><img class="nav" src="template/images/help.png" alt="{'help'|translate}"/></a>
	{/if}
{/if}
		</div>
{if count($languages) > 0}
        <div id="langSelect">
          <select onchange="document.location = this.options[this.selectedIndex].value;">
  {foreach from=$languages item=language_name key=language_code}
            <option
              value="{$self_uri}lang={$language_code}"
              {if ($lang == $language_code)}selected="selected"{/if}
            >
              {$language_name}
            </option>
  {/foreach}
          </select>
        </div>
{/if}
      {$main_content}
      </div>
    </div> <!-- overall -->
    
    <div id="footer">
      <a href="rss.php" title="notification feed">{'news feed'|translate}</a>
      - {'page generated in %s'|translate|sprintf:$generation_time}
      - {'powered by'|translate} {$subversion_revision}
    </div> <!-- footer -->

    {$footer}
  </body>
</html>
