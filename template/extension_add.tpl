<h2>{'Add/Modify an extension'|@translate}</h2>

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Extension informations'|@translate}</legend>

    <table>
      <tr>
        <th>{'Name'|@translate}</th>
        <td><input type="text" name="extension_name" size="35" maxlength="255" value="{$extension_name}" /></td>
      </tr>
      <tr>
        <th>{'Categories'|@translate}</th>
        <td>
          <div class="checkboxBox">
{foreach from=$extension_categories item=cat}
            <label><input type="checkbox" name="extension_category[]" value="{$cat.value}" {$cat.checked} /> {$cat.name}</label>
{/foreach}
          </div>
        </td>
      </tr>
      <tr>
        <th>{'Description'|@translate}</th>
        <td>
          <select onchange="show_lang_desc(this.options[this.selectedIndex].value);">
          {foreach from=$languages item=language}
            <option value="{$language.id}" {if $default_language == $language.id}selected="selected"{/if}>{$language.name}</option>
          {/foreach}
          </select>
          {foreach from=$languages item=language}
          <span id="desc_{$language.id}" class="desc">
            <input type="radio" name="default_description" value="{$language.id}" {if $default_language == $language.id}checked="ckecked"{/if}>{'Default description'|@translate}
            <br>
            <textarea cols="80" rows="10" name="extension_descriptions[{$language.id}]">{$extension_descriptions[$language.id]}</textarea>
          </span>
          {/foreach}
        </td>
      </tr>
    </table>

    <div>
      <input type="submit" value="{'Submit'|@translate}" name="submit" />
    </div>
  </fieldset>
</form>

{known_script id="jquery" src="template/jquery.min.js"}

<script type="text/javascript">
{literal}
function show_lang_desc(lang)
{
  $(".desc").hide();
  $("#desc_"+lang).show();
}
{/literal}

show_lang_desc({$default_language});
</script>
