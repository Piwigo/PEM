{known_script id="jquery" src="template/jquery.min.js"}
{known_script id="colorbox" src="template/colorbox/jquery.colorbox-min.js"}
{html_head}
<link rel="stylesheet" type="text/css" href="template/colorbox/colorbox.css">
<script type="text/javascript">
jQuery(function(){ldelim}
  jQuery('.screenshot').colorbox();
  jQuery('.flags-popup').colorbox({ldelim}inline:true});
});
</script>
{/html_head}

<div id="viewSelect">
  <select onchange="document.location = this.options[this.selectedIndex].value;">
    <option value="index.php?view=standard" selected="selected">{'standard view'|@translate}</option>
    <option value="index.php?view=compact">{'compact view'|@translate}</option>
  </select>
</div>

<h2>{$page_title}</h2>
<div class="pages">
  {if !empty($navbar) }{include file='navigation_bar.tpl'}{/if}
  <div class="Results">({$nb_total} {'extensions'|@translate})</div>
</div>

{foreach from=$revisions item=revision}
<div class="row" id="extension_{$revision.id}">
{if isset($revision.thumbnail_src)}
  <a class="screenshot" href="{$revision.screenshot_url}"><img src="{$revision.thumbnail_src}"/></a>
{/if}
  <p class="extension_title">
    <strong><a href="extension_view.php?eid={$revision.extension_id}">{$revision.extension_name}</a></strong>
    <div>{if isset($revision.rating_score)}{$revision.rating_score}{/if}
    {if isset($revision.nb_reviews)}<i style="vertical-align:top;">{$revision.nb_reviews}</i>{/if}</div>
  </p>

  <p><a href="{$revision.revision_url}">{'Revision'|@translate} {$revision.name}</a> ({$revision.date})</p>

  <ul>
    <li><em>{if count($revision.authors)>1}{'Authors'|@translate}{else}{'Author'|@translate}{/if}:</em> 
      {strip}{foreach from=$revision.authors item=u_name key=u_id name=authors_loop}
        {if not $smarty.foreach.authors_loop.first}, {/if}<a href="index.php?uid={$u_id}">{$u_name}</a>
      {/foreach}{/strip}
    </li>
    <li><em>{'Categories'|@translate}:</em> {$revision.categories}</li>
    {if !empty($revision.tags)}<li><em>{'Tags'|@translate}:</em> {$revision.tags}</li>{/if}
    {if !empty($revision.languages)}
    <li><em>{'Available languages'|@translate}:</em>
      <a href="#flags-{$revision.id}" class="flags-popup">{$revision.languages|@count} {'(see)'|translate}</a>
    </li>
    {/if}
    <li><em>{'Compatible with'|@translate}:</em> {$software} {'releases'|@translate} {$revision.compatible_versions}</li>
    <li><em>{'Downloads'|@translate}:</em> {$revision.downloads}</li>
  </ul>
  
  <div style="display:none">
    <table class="flags-table" id="flags-{$revision.id}"><tr>
    {foreach from=$revision.languages item=language name=langs}{strip}
      <td><span class="langflag-{$language.code}" title="{$language.name}"></span> {$language.name}</td>
      {if ($smarty.foreach.langs.index+1) % 3 == 0}</tr><tr>{/if}
    {/strip}{/foreach}
    </tr></table>
  </div>

  <p class="revision_about"><strong>{'About'|@translate}:</strong> {$revision.about}</p>

  <p class="revision_changes"><strong>{'Changes'|@translate}:</strong> {$revision.description}</p>
</div>
{/foreach}

<div class="pages">
  {if !empty($navbar) }{include file='navigation_bar.tpl'}{/if}
  <div class="Results">({$nb_total} {'extensions'|@translate})</div>
</div>
<div style="clear : both;"></div>