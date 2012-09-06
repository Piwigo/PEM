<h2>{'Add/Modify an extension'|@translate}</h2>

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Extension informations'|@translate}</legend>

    <table>
      <tr>
        <th>{'Name'|@translate}</th>
        <td><input type="text" name="extension_name" size="35" maxlength="255" value="{$extension_name}" {if $translator}disabled="disabled"{/if}/></td>
      </tr>
      <tr>
        <th>{'Categories'|@translate}</th>
        <td>
          <div class="checkboxBox">
            <select name="extension_category[]" id="extension_category" style="width:500px;" multiple="multiple">
            {foreach from=$extension_categories item=cat}
              <option value="{$cat.value}" {$cat.selected} {if $translator}disabled="disabled"{/if}>{$cat.name}</option>
            {/foreach}
            </select>
          </div>
        </td>
      </tr>
      <tr>
        <th>{'Tags'|@translate}</th>
        <td>
          <select id="tags" name="tags" multiple="multiple">
          {foreach from=$ext_tags item=tag}
            {if $tag.selected}
            <option value="{$tag.id_tag}" class="selected">{$tag.name}</option>
            {/if}
          {/foreach}
          </select>
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
            <label><input type="radio" name="default_description" value="{$language.id}" {if $default_language == $language.id}checked="checked"{/if} {if $translator}disabled="disabled"{/if}> {'Default description'|@translate}</label>
            <br>
            <textarea cols="80" rows="10" name="extension_descriptions[{$language.id}]" id="desc_{$language.id}" {if $translator and !$language.id|@in_array:$translator_languages}disabled="disabled"{/if}>{$descriptions[$language.id]}</textarea>
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
{known_script id="jquery.tokeninput" src="template/jquery.tokeninput.js"}
{known_script id="jquery.chosen" src="template/chosen.jquery.min.js"}
{html_head}
<link rel="stylesheet" type="text/css" href="template/chosen.css">
<link rel="stylesheet" type="text/css" href="template/jquery.tokeninput.css">
{/html_head}

<script type="text/javascript">
var languages = new Array();
var filled = new Array;
{foreach from=$languages item=language}
languages[{$language.id}] = "{$language.name}";
if ($('textarea[id=desc_{$language.id}]').val() != '')
  filled.push({$language.id});
{/foreach}

$(document).ready(function() {ldelim}
  $('select[name="lang_desc_select"]').change(function () {ldelim}
    $(".desc").hide();
    $("#span_"+this.options[this.selectedIndex].value).show();
  });
  $('input[name="default_description"]').click(function () {ldelim}
    set_default_description(this.value);
  });
  $('textarea[name^="extension_descriptions"]').keyup(function () {ldelim}
    arr = $(this).attr("id").split("desc_");
    id = arr[1];
    opt = $('select[name="lang_desc_select"] option[id="opt_'+id+'"]');
    if (this.value != '') {ldelim}
      opt.html(opt.html().replace("\u2718", "\u2714"));
      add = true;
      for (i in filled) {ldelim}
        if (filled[i] == id) add = false;
      }
      if (add) {ldelim}
        if (!filled.length) {ldelim}
          $('#span_'+id+' input[name="default_description"]').attr("checked", "checked");
          set_default_description(id);
        }
        filled.push(id);
      }
    }
    else {ldelim}
      for (i in filled) {ldelim}
        if (filled[i] == id) filled.splice(i, 1);
      }
      opt.html(opt.html().replace("\u2714", "\u2718"));
    }
  });
  
  $("#tags").tokenInput(
    [{foreach from=$ext_tags item=tag name=tags}{ldelim}"name":"{$tag.name|@escape:'javascript'}","id":"{$tag.id_tag}"{rdelim}{if !$smarty.foreach.tags.last},{/if}{/foreach}],
    {ldelim}
      hintText: '{'Type in a search term'|@translate}',
      noResultsText: '{'No results'|@translate}',
      searchingText: '{'Searching...'|@translate}',
      newText: ' ({'new'|@translate})',
      animateDropdown: false,
      preventDuplicates: true
      {if $allow_tag_creation}, allowCreation: true{/if}
    }
  );
  
  $('#extension_category').chosen();
});

function set_default_description (id) {ldelim}
  $(".default_description").html("{'Default description'|@translate}: "+languages[id]);
}

$("#span_"+{$default_language}).show();
set_default_description({$default_language});
</script>