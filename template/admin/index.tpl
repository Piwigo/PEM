<h1>Administration panel</h1>
<p><strong>{$revisions_count}</strong> revisions registered.</p>
{if (isset($empty_extensions_count))}
<p><a href="{$empty_extensions_url}">There are {$empty_extensions_count} extensions without a single revision</a></p>
{/if}

{if count($no_compat_revs) > 0}
<p>The following revisions are compatible with no version</p>
<ul>
  {foreach from=$no_compat_revs item=revision}
  <li><a href="../extension_view.php?eid={$revision.idx_extension}">{$revision.name}</a> {$revision.version} <a href="../revision_del.php?rid={$revision.id_revision}" target="_blank">delete now</a></li>
  {/foreach}
</ul>
{/if}