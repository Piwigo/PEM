<div id="viewSelect">
  <select onchange="document.location = this.options[this.selectedIndex].value;">
    <option value="index.php?view=standard" selected="selected">{'standard view'|translate}</option>
    <option value="index.php?view=compact">{'compact view'|translate}</option>
  </select>
</div>

<h2>{'Most recent extensions'|translate}</h2>
<div class="pages">
  <div class="paginationBar">{$pagination_bar}</div>
  <div class="Results">({$nb_total} {'extensions'|translate})</div>
</div>

{foreach from=$revisions item=revision}
<div class="row">
{if isset($revision.thumbnail_src)}
  <a class="screenshot" href="{$revision.screenshot_url}"><img src="{$revision.thumbnail_src}"/></a>
{/if}
  <p class="extension_title"><strong><a href="extension_view.php?eid={$revision.extension_id}">{$revision.extension_name}</a></strong></p>

  <p><a href="{$revision.revision_url}">{'Revision'|translate} {$revision.name}</a></p>

  <ul>
    <li><em>{'Author'|translate}:</em> {$revision.author}</li>
    <li><em>{'Categories'|translate}:</em> {$revision.categories}</li>
    <li><em>{'Released on'|translate}:</em> {$revision.date}</li>
    <li><em>{'Compatible with'|translate}:</em> {$software} releases {$revision.compatible_versions}</li>
    <li><em>{'Downloads'|translate}:</em> {$revision.downloads}</li>
  </ul>

  <p class="revision_about"><strong>{'About'|translate}:</strong> {$revision.about}</p>

  <p class="revision_changes"><strong>{'Changes'|translate}:</strong> {$revision.description}</p>
</div>
{/foreach}

<div class="pages">
  <div class="paginationBar">{$pagination_bar}</div>
  <div class="Results">({$nb_total} {'extensions'|translate})</div>
</div>
<div style="clear : both;"></div>