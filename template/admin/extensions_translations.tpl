<h2>Extensions translations</h2>

<style type="text/css">{literal}
.my-extensions thead td {
  font-weight:bold;
  padding:2px;
  background:#bbb;
  text-align:center;
}
.my-extensions tr td:first-child {
  min-width:0px;
  white-space:nowrap;
}
.my-extensions tr td:last-child {
  min-width:0px;
}
.my-extensions tr td:not(:first-child) {
  text-align:center;
}
.my-extensions thead tr td:not(:first-child) {
  font-size:10px;
}
.my-extensions td.main {
  text-shadow:0 0 3px #0f0, 0 0 8px #0f0, 0 0 15px #0f0;
}
{/literal}</style>


<form action="" method="post" style="margin-bottom:10px;">
<fieldset>
  <legend>Filter</legend>
  Category :
  <select name="category">
    <option value="-1">----------</option>
    {html_options options=$categories selected=$filter_category}
  </select>
  
  Version :
  <select name="version">
    <option value="-1">----------</option>
    {html_options options=$versions selected=$filter_version}
  </select>
  
  Name :
  <input name="name" value="{$filter_name}">
  
  <input type="submit" value="Filter">
  <input type="submit" name="reset" value="Reset">
</fieldset>
</form>

<table class="my-extensions">
  <thead>
    <tr>
      <td>Extension</td>
    {foreach from=$languages item=language}
      <td>{$language.name}</td>
    {/foreach}
    </tr>
  </thead>
  
  <tbody>
  {foreach from=$extensions item=extension name=foo}
    <tr class="{if $smarty.foreach.foo.index is odd}odd{else}even{/if}">
      <td><a href="../extension_view.php?eid={$extension.id}" target="_blank">{$extension.name}</a></td>
    {foreach from=$languages item=language}
      {if not in_array($language.id, $extension.all)}
      <td style="color:#d00;" {if $language.id==$extension.main}class="main"{/if}>&#x2718;</td>
      {else}
      <td style="color:#0a0;" {if $language.id==$extension.main}class="main"{/if}>&#x2714;</td>
      {/if}
    {/foreach}
    </tr>
  {foreachelse}
    <tr class="even"><td colspan="100">No results</td></tr>
  {/foreach}
  </tbody>
</table>