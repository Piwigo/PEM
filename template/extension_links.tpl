<h2>{$extension_name}</h2>

<h3>{'Manage links'|@translate}</h3>

<ul class="actionLinks">
  <li><a href="{$u_extension}" title="{'See extension'|@translate}"><img src="template/images/see_extension.png" alt="{'See extension'|@translate}"></a></li>
</ul>

{include file='infos_errors.tpl'}

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Add a link'|@translate}</legend>

    <table>
      <tr>
        <th><strong>{'Name'|@translate} *</strong></th>
        <td>
          <input type="text" name="link_name" maxlength="50" value="{$LINK_NAME}">
        </td>
      </tr>
      <tr>
        <th><strong>{'URL'|@translate} *</strong></th>
        <td>
          <input type="text" name="link_url" size="50" maxlength="255" value="{$LINK_URL}">
        </td>
      </tr>
      <tr><td>
      </td></tr>
      <tr>
        <th>{'Description'|@translate}</th>
        <td>
          <textarea cols="80" rows="3" name="link_description">{$LINK_DESC}</textarea>
        </td>
      </tr>
      {if !empty($languages)}
      <tr>
        <th><strong>{'Language'|@translate}</strong></th>
        <td>
          <select name="link_language">
            <option value="default">---------------</option>
            {foreach from=$languages item=language}
            <option value="{$language.id}" {if $LINK_LANG==$language.id}selected{/if}>{$language.name}</option>
            {/foreach}
          </select>
        </td>
      </tr>
      {/if}
    </table>

    <div>
      <input type="submit" value="{'Submit'|@translate}" name="submit_add" />
    </div>
  </fieldset>
</form>

{if count($links) > 0}
<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Manage links'|@translate}</legend>

    <ul class="linkManagement">
  {foreach from=$links item=link}
      <li>
        <a href="{$link.u_delete}" onclick="return confirm('{'Are you sure you want to delete this item?'|@translate|escape:javascript}');" class="linkAction" title="{'Delete link'|@translate}">
        <img src="template/images/delete.png" alt="{'Delete link'|@translate}"></a>
        <p>
          <strong><a href="{$link.url}">{$link.name}</a></strong>
          {$link.description}
        </p>
        <p>
          <label>
            {'Rank'|@translate}:
            <input type="text" name="linkRank[{$link.id}]" value="{$link.rank}" size="4"/>
          </label>
        </p>
      </li>
  {/foreach}
    </ul>

    <div>
      <input type="submit" value="{'Submit'|@translate}" name="submit_order" />
    </div>
  </fieldset>
</form>
{/if}