<div id="viewSelect">
  <select onchange="document.location = this.options[this.selectedIndex].value;">
    <option value="index.php?view=standard">standard view</option>
    <option value="index.php?view=compact" selected="selected">compact view</option>
  </select>
</div>

<h2>Most recent extensions</h2>
<div class="pages">
  <div class="Results">({$nb_total} extensions)</div>
</div>

{if count($revisions) > 0}
<ul>
{foreach from=$revisions item=revision}
  <li>
    [{$revision.date}] <a href="{$revision.revision_url}">{$revision.extension_name}-{$revision.name}</a> -- {$revision.about|truncate}
  </li>
{/foreach}
</ul>
{/if}

<div class="pages">
  <div class="Results">({$nb_total} extensions)</div>
</div>
<div style="clear : both;"></div>