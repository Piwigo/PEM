<div class="paginationBar">
  {if isset($navbar.URL_FIRST)}
  <a href="{$navbar.URL_FIRST}" rel="first" class="FirstActive">&lt;&lt; {'first'|@translate}</a>
  <a href="{$navbar.URL_PREV}" rel="prev" class="PrevActive">&lt; {'prev'|@translate}</a>
  {else}
  <span class="FirstInactive">&lt;&lt; {'first'|@translate}</span>
  <span class="PrevInactive">&lt; {'prev'|@translate}</span>
  {/if}

  {assign var='prev_page' value=0}
  {foreach from=$navbar.pages key=page item=url}
    {if $page > $prev_page+1}
    <span class="inactive">...</span>
    {/if}
    {if $page == $navbar.CURRENT_PAGE}
    <span class="currentPage">{$page}</span>
    {else}
    <a href="{$url}">{$page}</a>
    {/if}
    {assign var='prev_page' value=$page}
  {/foreach}

  {if isset($navbar.URL_NEXT)}
  <a href="{$navbar.URL_NEXT}" rel="next" class="NextActive">{'next'|@translate} &gt;</a>
  <a href="{$navbar.URL_LAST}" rel="last" class="LastActive">{'last'|@translate} &gt;&gt;</a>
  {else}
  <span class="NextInactive">{'next'|@translate} &gt;</span>
  <span class="LastInactive">{'last'|@translate} &gt;&gt;</span>
  {/if}
</div>
