<h2>{$extension_name}</h2>

<h3>{'Manage authors'|@translate}</h3>

<ul class="actionLinks">
  <li><a href="{$u_extension}" title="{'See extension'|@translate}"><img src="template/images/see_extension.png" alt="{'See extension'|@translate}"></a></li>
</ul>

{include file='infos_errors.tpl'}

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Add an author'|@translate}</legend>
    <p>&nbsp;</p>
    <strong>{'Name'|@translate}</strong>
    {html_options name=author_select options=$users}
    <input type="submit" value="{'Submit'|@translate}" name="submit_add" />
    <p>&nbsp;</p>
  </fieldset>
<p>&nbsp;</p>
  <fieldset>
    <legend>{'Manage authors'|@translate}</legend>
    <p>&nbsp;</p>
    {foreach from=$authors item=author}
      <strong>{$author.NAME}</strong>
      {if not $author.OWNER}
        <a href="{$author.u_delete}">{'Delete'|translate}</a>
        {if isset($author.u_owner)}| <a href="{$author.u_owner}">{'Set as owner'|translate}</a>{/if}
      {else}
        <i>({'Owner'|translate})</i>
      {/if}
      <br>
    {/foreach}
    <p>&nbsp;</p>
  </fieldset>
</form>