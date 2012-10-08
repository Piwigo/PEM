<h2>{'My personnal homepage'|@translate}</h2>

<!-- Used to fix a margin bug with IE... -->
<br />

<p>{'My extensions'|@translate}:</p>
{if count($extensions) > 0}
<table class="my-extensions">
  <thead>
    <tr>
      <td>{'Name'|@translate}</td>
      <td>{'Revision'|@translate}</td>
      <td>{'Downloads'|@translate}</td>
      <td>{'Reviews'|@translate}</td>
      <td>{'Score'|@translate}</td>
    </tr>
  </thead>
  <tbody>
  {foreach from=$extensions item=extension name=foo}
    <tr class="{if $smarty.foreach.foo.index is odd}odd{else}even{/if}">
      <td><a href="extension_view.php?eid={$extension.id}">{$extension.name}</a></td>
      <td style="text-align:center;">{$extension.revision}</td>
      <td style="text-align:right;">{$extension.nb_downloads}</td>
      <td style="text-align:right;">{$extension.nb_reviews}</td>
      <td style="text-align:center;">{if $extension.total_rates}{$extension.rating_score} ({$extension.total_rates}){/if}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}

{if count($other_extensions) > 0}
<br />
<p>{'Other extensions'|@translate}:</p>
<table class="my-extensions">
  <thead>
    <tr>
      <td>{'Name'|@translate}</td>
      <td>{'Revision'|@translate}</td>
      <td>{'Downloads'|@translate}</td>
      <td>{'Reviews'|@translate}</td>
      <td>{'Score'|@translate}</td>
    </tr>
  </thead>
  <tbody>
  {foreach from=$other_extensions item=extension name=foo}
    <tr class="{if $smarty.foreach.foo.index is odd}odd{else}even{/if}">
      <td><a href="extension_view.php?eid={$extension.id}">{$extension.name}</a></td>
      <td style="text-align:center;">{$extension.revision}</td>
      <td style="text-align:right;">{$extension.nb_downloads}</td>
      <td style="text-align:right;">{$extension.nb_reviews}</td>
      <td style="text-align:center;">{if $extension.total_rates}{$extension.rating_score} ({$extension.total_rates}){/if}</td>
    </tr>
  {/foreach}
  </tbody>
</table>
{/if}

<div style="clear : both;"></div>