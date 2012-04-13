{known_script id="jquery" src="template/jquery.min.js"}
{known_script id="jquery.raty" src="template/jquery.raty/jquery.raty.min.js"}
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

<h2>{$extension_name}</h2>

{if isset($can_modify)}
<ul class="actionLinks">
  <li><a href="{$u_modify}" title="{'Modify extension'|@translate}"><img src="template/images/modify.png" alt="{'Modify extension'|@translate}"></a></li>
{if !$translator}
  {if isset($u_delete)}
    <li><a href="{$u_delete}" onclick="return confirm('{'Are you sure you want to delete this item?'|@translate|escape:javascript}');" title="{'Delete extension'|@translate}"><img src="template/images/delete.png" alt="{'Delete extension'|@translate}"></a></li>
  {/if}
  <li><a href="{$u_links}" title="{'Manage links'|@translate}"><img src="template/images/links.png" alt="{'Manage links'|@translate}"></a></li>
  <li><a href="{$u_screenshot}" title="{'Manage screenshots'|@translate}"><img src="template/images/screenshot.png" alt="{'Manage screenshots'|@translate}"></a></li> 
  <li><a href="{$u_add_rev}" title="{'Add a revision'|@translate}"><img src="template/images/add_revision.png" alt="{'Add a revision'|@translate}"></a></li>
  {if isset($u_authors)}
    <li><a href="{$u_authors}" title="{'Manage authors'|@translate}"><img src="template/images/authors.png" alt="{'Manage authors'|@translate}"></a></li>
  {/if}
  {if isset($u_svn)}
    <li><a href="{$u_svn}" title="{'SVN configuration'|@translate}"><img src="template/images/svn.png" alt="{'SVN configuration'|@translate}"></a></li>
  {/if}
{/if}
</ul>
{/if}

<div class="extensionButtons">
  <div class="extensionRate">
    <em>{'Average rating'|@translate} :</em>
  {if $rate_summary.rating_score == NULL}
    <div class="rating_infos">{'not rated yet'|@translate}</div>
  {else}
  {html_head}
    <script type="text/javascript">
    $(document).ready(function() {ldelim}
      $('#average_rating').raty({ldelim}
        path: "template/jquery.raty/",
        readOnly: true,
        start: {$rate_summary.rating_score}
      });
    });
    </script>
  {/html_head}
    <div id="average_rating"></div>
    <div class="rating_infos">{$rate_summary.count_text}</div>
  {/if}
    
  {html_head}
    <script type="text/javascript">
    $(document).ready(function() {ldelim}
      $('#user_rate div').raty({ldelim}
        path: "template/jquery.raty/",
        cancelHint: '{'cancel this rating!'|@translate}',
        cancelPlace: 'right',
        size:18,
        half: true,
        click: function(score, event) {ldelim}
          $("#user_rate").append('<input type="hidden" name="rate" value="'+ score +'">').submit(); 
        }
        {if isset($user_rating.rate)}, cancel: true, start: {$user_rating.rate}{/if}
      });
      $("#user_rate_show").click(function() {ldelim}
        $(this).slideUp();
        $('#user_rate').slideDown();
      });
    });
    </script>
  {/html_head}
    <br>
    <a id="user_rate_show">{if isset($user_rating.rate)}{'Update your rating'|@translate}{else}{'Rate it!'|@translate}{/if}</a>
    <form id="user_rate" style="display:none;" method="post" action="{$user_rating.action}">
      <em>{'Your rating'|@translate} :</em>
      <div></div>
    </form>
  </div>
  
{if isset($download_last_url)}
  <div class="downloadButton"><a href="{$download_last_url}" title="{'Download last revision'|@translate}">{'Download'|@translate}</a></div>
{/if}
{if isset($thumbnail)}
<a class="screenshot highslide" href="{$thumbnail.url}" onclick="return hs.expand(this)"><img src="{$thumbnail.src}"/></a>
{/if}
</div>
<ul class="extensionInfos">
  <li><em>{if count($authors)>1}{'Authors'|@translate}{else}{'Author'|@translate}{/if}:</em> {', '|@implode:$authors}</li>
  <li><em>{'Categories'|@translate}:</em> {$extension_categories}</li>
  <li><em>{'Tags'|@translate}:</em> {$extension_tags}</li>
  <li><em>{'First revision date'|@translate}:</em> {$first_date}</li>
  <li><em>{'Latest revision date'|@translate}:</em> {$last_date}</li>
  <li><em>{'Compatible with'|@translate}:</em> {$software} {'releases'|@translate} {$compatible_with}</li>
  <li><em>{'Downloads'|@translate}:</em> {$extension_downloads}</li>
</ul>

<p><strong>{'About'|@translate}:</strong> {$description}</p>

{if count($links) > 0}
<h3>{'Related links'|@translate}</h3>

<ul>
  {foreach from=$links item=link}
  <li><strong><a href="{$link.url}">{$link.name}</a></strong>: {$link.description}</li>
  {/foreach}
</ul>
{/if}

<h3 id="revisionListTitle">{'Revision list'|@translate}</h3>

<p class="listButton">
  <a onclick="fullToggleDisplay()" class="javascriptButton">{'expand/collapse all'|@translate}</a>
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
      <span class="revisionTitle">{'Revision'|@translate} {$rev.version}</span>
      <span class="revisionDate"> {$rev.downloads} {'Downloads'|@translate}, {'Released on'|@translate} {$rev.date}</span>
    </div>

    <div
      id="rev{$rev.id}_content"
      class="changelogRevisionContent"
  {if !$rev.expanded}
      style="display:none"
  {/if}
    >
      <a href="{$rev.u_download}" title="{'Download revision'|@translate} {$rev.version}" rel="nofollow"><img class="download" src="template/images/download.png" alt="{'Download revision'|@translate} {$rev.version}"/></a>
      <p><em>{'Compatible with'|@translate}:</em> {$rev.versions_compatible}</p>
  {if !empty($rev.languages)}
      <p><em>{'Available languages'|@translate}:</em>
        {foreach from=$rev.languages item=language}
          <img class="icon" src="language/{$language.code}/icon.jpg" alt="{$language.name}" title="{$language.name}">
        {/foreach}
      </p>
  {/if}
  {if !empty($rev.author)}
      <p><em>{'Added by'|@translate}:</em> {$rev.author}</p>
  {/if}
    
      <blockquote>
        <p>{$rev.description}</p>
      </blockquote>

  {if $rev.can_modify}
      <ul class="revActionLinks">
        <li><a href="{$rev.u_modify}" title="{'Modify revision'|@translate}"><img src="template/images/modify.png" alt="{'Modify revision'|@translate}"></a></li>
        {if !$translator}
        <li><a href="{$rev.u_delete}" onclick="return confirm('{'Are you sure you want to delete this item?'|@translate|escape:javascript}');" title="{'Delete revision'|@translate}">
            <img src="template/images/delete.png" alt="{'Delete revision'|@translate}"></a></li>
        {/if}
      </ul>
  {/if}
    </div>
  </div> <!-- rev{$rev.id} -->
  {/foreach}
</div> <!-- changelog -->
{else}
<p><em>{'No revision available for this extension.'|@translate}</em></p>
{/if}
