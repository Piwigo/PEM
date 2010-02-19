<h2>Empty extensions</h2>

{if count($extensions) > 0}
<ul>
  {foreach from=$extensions item=extension}
  <li><a href="../extension_view.php?eid={$extension.id_extension}">{$extension.name}</a></li>
  {/foreach}
</ul>
{/if}