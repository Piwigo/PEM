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
          <select name="lang_desc_select">
          {foreach from=$languages item=language}
            <option value="{$language.id}" id="opt_{$language.id}" {if $default_language == $language.id}selected="selected"{/if}>
              {if empty($descriptions[$language.id])}&#x2718;{else}&#x2714;{/if} &nbsp;{$language.name} </option>
          {/foreach}
          </select>
          {foreach from=$languages item=language}
          <span id="span_{$language.id}" class="desc" style="display: none;"> &nbsp;
            <label><input type="radio" name="default_description" value="{$language.id}" {if $default_language == $language.id}checked="checked"{/if}> {'Default description'|@translate}</label>
            <br>
            <textarea cols="80" rows="10" name="extension_descriptions[{$language.id}]" id="desc_{$language.id}">{$descriptions[$language.id]}</textarea>
          </span>
          {/foreach}
          <p class="default_description"></p>
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
var languages = new Array();
{foreach from=$languages item=language}
languages[{$language.id}] = "{$language.name}";
{/foreach}

$(document).ready(function() {ldelim}
  $('select[name="lang_desc_select"]').change(function () {ldelim}
    $(".desc").hide();
    $("#span_"+this.options[this.selectedIndex].value).show();
  });
  $('input[name="default_description"]').click(function () {ldelim}
    $(".default_description").html("{'Default description'|@translate}: "+languages[this.value]);
  });
  $('textarea[name^="extension_descriptions"]').keyup(function () {ldelim}
    arr = $(this).attr("id").split("desc_");
    opt = $('select[name="lang_desc_select"] option[id="opt_'+arr[1]+'"]');
    if (this.value != '') {ldelim}
      opt.html(opt.html().replace("\u2718", "\u2714"));
    }
    else {ldelim}
      opt.html(opt.html().replace("\u2714", "\u2718"));
    }
  });
});

$("#span_"+{$default_language}).show();
$(".default_description").html("{'Default description'|@translate}: "+languages[{$default_language}]+"");
</script>
