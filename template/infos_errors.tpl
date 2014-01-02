{if isset($errors)}
<div class="messages errors">
  <ul>
    {foreach from=$errors item=error}
    <li>{$error}</li>
    {/foreach}
  </ul>
</div>
{/if}
{if isset($warnings)}
<div class="messages warnings">
  <ul>
    {foreach from=$warnings item=warning}
    <li>{$warning}</li>
    {/foreach}
  </ul>
</div>
{/if}
{if not empty($infos)}
<div class="messages infos">
  <ul>
    {foreach from=$infos item=info}
    <li>{$info}</li>
    {/foreach}
  </ul>
</div>
{/if}