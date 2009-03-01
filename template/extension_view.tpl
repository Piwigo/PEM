<h2>{$extension_name}</h2>

{if isset($can_modify)}
<ul class="actionLinks">
  <li><a href="{$u_modify}" title="{'Modify extension'|translate}"><img src="template/images/modify.png" alt="{'Modify extension'|translate}"></a></li>
  {if isset($u_delete)}
    <li><a href="{$u_delete}" onclick="return confirm('{'Are you sure you want to delete this item?'|translate|escape:javascript}');" title="{'Delete extension'|translate}"><img src="template/images/delete.png" alt="{'Delete extension'|translate}"></a></li>
  {/if}
  <li><a href="{$u_links}" title="{'Manage links'|translate}"><img src="template/images/links.png" alt="{'Manage links'|translate}"></a></li>
  <li><a href="{$u_screenshot}" title="{'Manage screenshots'|translate}"><img src="template/images/screenshot.png" alt="{'Manage screenshots'|translate}"></a></li> 
  <li><a href="{$u_add_rev}" title="{'Add a revision'|translate}"><img src="template/images/add_revision.png" alt="{'Add a revision'|translate}"></a></li>
  {if isset($u_authors)}
    <li><a href="{$u_authors}" title="{'Manage authors'|translate}"><img src="template/images/authors.png" alt="{'Manage authors'|translate}"></a></li>
  {/if}
</ul>
{/if}

{if isset($thumbnail)}
<a class="screenshot" href="{$thumbnail.url}"><img src="{$thumbnail.src}"/></a>
{/if}

<ul class="extensionInfos">
  <li><em>{if count($authors)>1}{'Authors'|translate}{else}{'Author'|translate}{/if}:</em> {', '|@implode:$authors}</li>
  <li><em>{'Categories'|translate}:</em> {$extension_categories}</li>
  <li><em>{'First revision date'|translate}:</em> {$first_date}</li>
  <li><em>{'Latest revision date'|translate}:</em> {$last_date}</li>
  <li><em>{'Compatible with'|translate}:</em> {$software} releases {$compatible_with}</li>
  <li><em>{'Downloads'|translate}:</em> {$extension_downloads}</li>
</ul>

<p><strong>{'About'|translate}:</strong> {$description}</p>

{if count($links) > 0}
<h3>{'Related links'|translate}</h3>

<ul>
  {foreach from=$links item=link}
  <li><strong><a href="{$link.url}">{$link.name}</a></strong>: {$link.description}</li>
  {/foreach}
</ul>
{/if}

<h3 id="revisionListTitle">{'Revision list'|translate}</h3>

<p class="listButton">
  <a onclick="fullToggleDisplay()" class="javascriptButton">{'expand/collapse all'|translate}</a>
</p>

{if isset($revisions)}
<div id="changelog">
  {foreach from=$revisions item=rev}
  <div id="rev{$rev.id}" class="changelogRevision">

    <div
      id="rev{$rev.id}_header"
  {if $rev.expanded}
      class="changelogRevisionHeaderExpanded"
  {else}
      class="changelogRevisionHeaderCollapsed"
  {/if}
      onclick="revToggleDisplay('rev{$rev.id}_header', 'rev{$rev.id}_content')"
    >
      <span class="revisionTitle">{'Revision'|translate} {$rev.version}</span>
      <span class="revisionDate"> {$rev.downloads} {'Downloads'|translate}{if !empty($rev.author)}, {'Added by'|translate} {$rev.author}{/if}, {'Released on'|translate} {$rev.date}</span>
    </div>

    <div
      id="rev{$rev.id}_content"
      class="changelogRevisionContent"
  {if !$rev.expanded}
      style="display:none"
  {/if}
    >
      <a href="{$rev.u_download}" title="{'Download revision'|translate} {$rev.version}" rel="nofollow"><img class="download" src="template/images/download.png" alt="{'Download revision'|translate} {$rev.version}"/></a>
      <p><em>{'Compatible with'|translate}:</em> {$rev.versions_compatible}</p>
    
      <blockquote>
        <p>{$rev.description}</p>
      </blockquote>

  {if $rev.can_modify}
      <ul class="revActionLinks">
        <li><a href="{$rev.u_modify}" title="{'Modify revision'|translate}"><img src="template/images/modify.png" alt="{'Modify revision'|translate}"></a></li>
        <li><a href="{$rev.u_delete}" onclick="return confirm('{'Are you sure you want to delete this item?'|translate|escape:javascript}');" title="{'Delete revision'|translate}">
            <img src="template/images/delete.png" alt="{'Delete revision'|translate}"></a></li>
      </ul>
  {/if}
    </div>
  </div> <!-- rev{$rev.id} -->
  {/foreach}
</div> <!-- changelog -->
{else}
<p><em>{'No revision available for this extension.'|translate}</em></p>
{/if}
