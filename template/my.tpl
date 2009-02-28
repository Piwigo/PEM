<h2>{'My personnal homepage'|translate}</h2>

<!-- Used to fix a margin bug with IE... -->
<br />

<p>{'My extensions'|translate}:</p>
{if count($extensions) > 0}
<ul>
  {foreach from=$extensions item=extension}
  <li><a href="extension_view.php?eid={$extension.id}">{$extension.name}</a></li>
  {/foreach}
</ul>
{/if}

{if count($other_extensions) > 0}
<br />
<p>{'Other extensions'|translate}:</p>
<ul>
  {foreach from=$other_extensions item=extension}
  <li><a href="extension_view.php?eid={$extension.id}">{$extension.name}</a></li>
  {/foreach}
</ul>
{/if}

<div style="clear : both;"></div>