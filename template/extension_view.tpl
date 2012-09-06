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

$(document).ready(function() {ldelim}
  $('#user_rate div').raty({ldelim}
    path: "template/jquery.raty/",
    cancelHint: '{'cancel this rating!'|@translate}',
    cancelPlace: 'right',
    size:18, half: true,
    click: function(score, event) {ldelim}
      $("#user_rate").submit(); 
    }
    {if isset($user_rating.rate)}, cancel: true, start: {$user_rating.rate}{/if}
  });
  $("#user_rate_show").click(function() {ldelim}
    $(this).slideUp();
    $("#user_rate").slideDown();
  });
  
  $("#review_form #review_rate").raty({ldelim}
    path: "template/jquery.raty/",
    half: true
    {if isset($user_review.rate)}, start: {$user_review.rate}{elseif isset($user_rating.rate)}, start: {$user_rating.rate}{/if}
  });
  $("#review_form_show").click(function() {ldelim}
    $("#review_form").slideToggle();
    return false;
  });
  $("#review_form .language a").click(function() {ldelim}
    $("#review_form .language").toggle();
    return false;
  });
  
  $("#review_form").submit(function() {ldelim}
    if ($("#review_form input[name='author']").val() == '') {ldelim}
      alert("{'Please enter your name'|@translate}");
      return false;
    }
    if ($("#review_form input[name='email']").val() == '') {ldelim}
      alert("{'Please enter your email'|@translate}");
      return false;
    }
    if ($("#review_form textarea[name='content']").val() == '') {ldelim}
      alert("{'The review is empty!'|@translate}");
      return false;
    }
    if ($("#review_form input[name='score']").val() == '') {ldelim}
      alert("{'Please rate this extension'|@translate}");
      return false;
    }
  });
  
  // restore interface for users with JS
  {if !isset($user_review.display)}
  $("#review_form").hide();
  {/if}
  $("select[name='score']").remove();
  $("#user_rate_show").show();
});
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

{if isset($user_review.message)}<div class="review_message {$user_review.action}">{$user_review.message}</div>{/if}

<div class="extensionButtons">
{if isset($download_last_url)}
  <div class="downloadButton"><a href="{$download_last_url}" title="{'Download last revision'|@translate}">{'Download'|@translate}</a></div>
{/if}

  <div class="extensionRate">
    <em>{'Average rating'|@translate} :</em>
  {if $rate_summary.rating_score == NULL}
    <div class="rating_infos">{'not rated yet'|@translate}</div>
  {else}
    <div id="average_rating">{$rate_summary.rating_score}</div>
    <div class="rating_infos">{$rate_summary.count_text}</div>
  {/if}
    <br>
    <a id="user_rate_show" style="display:none;">{if isset($user_rating.rate)}{'Update your rating'|@translate}{else}{'Rate it!'|@translate}{/if}</a>
    <form id="user_rate" style="display:none;" method="post" action="{$user_rating.action}">
      <em>{'Your rating'|@translate} :</em>
      <div></div>
    </form>
  </div>
  
{if isset($thumbnail)}
<a class="screenshot highslide" href="{$thumbnail.url}" onclick="return hs.expand(this)"><img src="{$thumbnail.src}"/></a>
{/if}

</div>
<ul class="extensionInfos">
  <li><em>{if count($authors)>1}{'Authors'|@translate}{else}{'Author'|@translate}{/if}:</em> 
    {strip}{foreach from=$authors item=u_name key=u_id name=authors_loop}
      {if not $smarty.foreach.authors_loop.first}, {/if}<a href="index.php?uid={$u_id}">{$u_name}</a>
    {/foreach}{/strip}
  </li>
  <li><em>{'Categories'|@translate}:</em> {$extension_categories}</li>
  {if !empty($extension_tags)}<li><em>{'Tags'|@translate}:</em> {$extension_tags}</li>{/if}
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

<!-- reviews -->
{if $nb_reviews > 0}
<h3>
  {$nb_reviews} {'Reviews'|@translate} 
  <a id="review_form_show" name="add_review" style="font-size:0.8em;"><img src="template/images/comment.gif"> {'Add a review'|@translate}</a>
</h3>
{else}
<h3>
  <a id="review_form_show" name="add_review"><img src="template/images/comment.gif"> {'Add a review'|@translate}</a>
</h3>
{/if}

<form id="review_form" method="post" action="{$user_review.form_action}">
  <p class="review_message warning">{'Please do not use this form to request assistance or report a bug. Use the forums instead.'|@translate}</p>
  <p {if isset($user_review.is_logged)}style="display:none;"{/if}>
    <label for="author">{'Name'|@translate}</label><br> 
    <input id="author" type="text" name="author" size="30" value="{$user_review.author}">
  </p>
  <p {if isset($user_review.is_logged)}style="display:none;"{/if}>
    <label for="email">{'Email (not displayed)'|@translate}</label><br> 
    <input id="email" type="text" name="email" size="30" value="{$user_review.email}">
  </p>
  <p>
    <label for="title">{'Review summary'|@translate}</label><br> 
    <input id="title" type="text" name="title" style="width:99%;" value="{$user_review.title}">
  </p>
  <p>
    <label for="content">{'Your review'|@translate}</label><br> 
    <textarea id="content" name="content" style="width:99%;" rows="6">{$user_review.content}</textarea>
  </p>
  <p>
    <label>{'Your rating'|@translate}</label>
    <span id="review_rate"></span>
    {html_options name=score options=$scores}
  </p>
  <p>
    <label>{'Language'|@translate}</label> 
    <span class="language"><b>{$languages[$user_language].name}</b> <a href="#">This is not your language ?</a></span>
    <span class="language" style="display:none;">
      <select name="idx_language">
        <option value="0">-- {'Other'|@translate} --</option>
      {foreach from=$languages item=language}
        <option value="{$language.id}" {if $languages[$user_language].id == $language.id}selected="selected"{/if}>{$language.name}</option>
      {/foreach}
      </select>
    </span>
  </p>
  <p><br><input type="submit" value="{'Send'|@translate}"></p>
</form>

{if $nb_reviews > 0}
<a name="reviews"></a>
<div id="reviews_list">
{foreach from=$reviews item=review name=review_loop}
  <div class="reviewItem {if $smarty.foreach.review_loop.index is even}odd{else}even{/if}">
  {if $review.in_edit}
    <a name="review_edit"></a>
    <form method="post" action="{$review.action}">
  {/if}
    <div class="reviewInfos">
      <div class="rating">{$review.rate}</div>
      <div class="author">
        {if $review.email}<a href="mailto:{$review.email}">{$review.author}</a>{else}{$review.author}{/if}
        on {$review.date}
        {if isset($review.u_delete)}| <a href="{$review.u_delete}#reviews">{'Delete'|@translate}</a>{/if}
        {if isset($review.u_edit)}| <a href="{$review.u_edit}#review_edit">{'Edit'|@translate}</a>{/if}
        {if isset($review.u_cancel)}| <a href="{$review.u_cancel}#reviews">{'Cancel'|@translate}</a>{/if}
        {if isset($review.u_validate)}| <a href="{$review.u_validate}#reviews">{'Validate'|@translate}</a>{/if}
      </div>
      <div class="title">
      {if $review.in_edit}
        <input id="title" type="text" name="title" style="width:600px;" value="{$review.title}">
      {else}
        {$review.title}
      {/if}
      </div>
    </div>
  {if $review.in_edit}
      <textarea id="content" name="content" style="width:600px;" rows="6">{$review.content}</textarea>
      <input type="hidden" name="id_review" value="{$review.id_review}">
      <br><input type="submit" value="{'Send'|@translate}">
    </form>
  {else}
    <blockquote>{$review.content}</blockquote>
  {/if}
  </div>
{/foreach}
</div>

{if $U_DISPLAY_ALL_REVIEWS}<a id="displayAllReviews" href="{$U_DISPLAY_ALL_REVIEWS}">+ {'Show %d more reviews'|@translate|sprintf:$NB_REVIEWS_MASKED}</a>{/if}
{/if}