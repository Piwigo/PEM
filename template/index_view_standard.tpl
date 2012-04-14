{known_script id="highslide" src="template/highslide/highslide-full.packed.js"}
{html_head}
<link rel="stylesheet" type="text/css" href="template/highslide/highslide.css">
<script type="text/javascript">
hs.graphicsDir = 'template/highslide/graphics/';
hs.registerOverlay({ldelim}
  html: '<div class="closebutton" onclick="return hs.close(this)"></div>',
  position: 'top right',
  fade: 2
});
hs.align = 'center';
hs.showCredits = false;
hs.outlineType = 'rounded-white';
hs.expandDuration = 400;
hs.allowSizeReduction = false;
hs.lang['restoreTitle'] = '';
</script>
{/html_head}

<div id="viewSelect">
  <select onchange="document.location = this.options[this.selectedIndex].value;">
    <option value="index.php?view=standard" selected="selected">{'standard view'|@translate}</option>
    <option value="index.php?view=compact">{'compact view'|@translate}</option>
  </select>
</div>

<h2>{'Most recent extensions'|@translate}</h2>
<div class="pages">
  {if !empty($navbar) }{include file='navigation_bar.tpl'}{/if}
  <div class="Results">({$nb_total} {'extensions'|@translate})</div>
</div>

{foreach from=$revisions item=revision}
<div class="row" id="extension_{$revision.id}">
{if isset($revision.thumbnail_src)}
  <a class="screenshot highslide" href="{$revision.screenshot_url}" onclick="return hs.expand(this)"><img src="{$revision.thumbnail_src}"/></a>
{/if}
  <p class="extension_title">
    <strong><a href="extension_view.php?eid={$revision.extension_id}">{$revision.extension_name}</a></strong>
    {if isset($revision.rating_score)}<div class="rating_score">{$revision.rating_score}</div>{/if}
  </p>

  <p><a href="{$revision.revision_url}">{'Revision'|@translate} {$revision.name}</a></p>

  <ul>
    <li><em>{if count($revision.authors)>1}{'Authors'|@translate}{else}{'Author'|@translate}{/if}:</em> {', '|@implode:$revision.authors}</li>
    <li><em>{'Categories'|@translate}:</em> {$revision.categories}</li>
    {if !empty($revision.tags)}<li><em>{'Tags'|@translate}:</em> {$revision.tags}</li>{/if}
    <li><em>{'Released on'|@translate}:</em> {$revision.date}</li>
    <li><em>{'Compatible with'|@translate}:</em> {$software} {'releases'|@translate} {$revision.compatible_versions}</li>
    {if !empty($revision.languages)}
    <li><em>{'Available languages'|@translate}:</em>
        {foreach from=$revision.languages item=language}
          <img class="icon" src="language/{$language.code}/icon.jpg" alt="{$language.name}" title="{$language.name}">
        {/foreach}
    </li>
    {/if}
    <li><em>{'Downloads'|@translate}:</em> {$revision.downloads}</li>
  </ul>

  <p class="revision_about"><strong>{'About'|@translate}:</strong> {$revision.about}</p>

  <p class="revision_changes"><strong>{'Changes'|@translate}:</strong> {$revision.description}</p>
</div>
{/foreach}

<div class="pages">
  {if !empty($navbar) }{include file='navigation_bar.tpl'}{/if}
  <div class="Results">({$nb_total} {'extensions'|@translate})</div>
</div>
<div style="clear : both;"></div>