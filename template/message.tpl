<h2>{$message_title}</h2>
<p>{$message_text}</p>

{if isset($u_redirect)}
<p>{'Automatic redirection in %s seconds.'|translate|sprintf:$time_redirect}</p>
<p><a href="{$u_redirect}">{'Click here if don\'t want to wait.'|translate}</a></p>
{/if}

{if $go_back}
<p><a href="javascript:history.back();">{'Back to previous page'|translate}</a>.</p>
{/if}
