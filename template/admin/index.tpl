<h1>Administration panel</h1>
<p><strong>{$revisions_count}</strong> revisions registered.</p>
{if (isset($empty_extensions_count))}
<p><a href="{$empty_extensions_url}">There are {$empty_extensions_count} extensions without a single revision</a></p>
{/if}