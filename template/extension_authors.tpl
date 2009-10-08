<h2>{$extension_name}</h2>

<h3>{'Manage authors'|@translate}</h3>

<ul class="actionLinks">
  <li><a href="{$u_extension}" title="{'See extension'|@translate}"><img src="template/images/see_extension.png" alt="{'See extension'|@translate}"></a></li>
</ul>

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Add an author'|@translate}</legend>
    <p>&nbsp;</p>
    <strong>{'Name'|@translate}</strong>
    {html_options name=author_select options=$users}
    <input type="submit" value="{'Submit'|@translate}" name="submit_add" />
    <p>&nbsp;</p>
  </fieldset>
<br>
{if !empty($authors)}
  <fieldset>
    <legend>{'Manage authors'|@translate}</legend>
    <br>
    {foreach from=$authors item=author}
      <input type="checkbox" name="author_id[]" value="{$author.ID}">&nbsp; <strong>{$author.NAME}</strong><br>
    {/foreach}
    <p>&nbsp;</p>
    <p><input type="submit" onclick="return confirm('{'Are you sure you want to remove selected authors?'|@translate|escape:javascript}');" value="{'Delete selected users'|@translate}" name="submit_delete" /></p>
  </fieldset>
{/if}
</form>