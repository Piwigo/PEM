<h2>Reviews</h2>

<p><b>{$nb_reviews}</b> reviews awaiting validation</p>

{if !empty($reviews)}
<div id="reviews_list">
  {foreach from=$reviews item=review name=review_loop}
  <div class="reviewItem {if $smarty.foreach.review_loop.index is odd}odd{else}even{/if}">
    <div class="reviewInfos">
       <div class="rating">extension: <b>{$review.extension_name}</b></div>
      <div class="author">
        {$review.author} on {$review.date}
        {if isset($review.u_delete)}| <a href="{$review.u_delete}">Delete</a>{/if}
        {if isset($review.u_validate)}| <a href="{$review.u_validate}">Validate</a>{/if}
      </div>
      <div class="title">{$review.title}</div>
    </div>
    <blockquote>{$review.content}</blockquote>
  </div>
  {/foreach}
</div>
{/if}
</ul>
