<h2>{$extension_name}</h2>

<h3>{$page_title}</h3>

<ul class="actionLinks">
  <li><a href="{$u_extension}" title="{'See extension'|@translate}"><img src="template/images/see_extension.png" alt="{'See extension'|@translate}"></a></li>
</ul>

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{$page_title}</legend>

    <table>
      <tr>
        <th>{'Version'|@translate}</th>
        <td>
        {if $IN_EDIT}
          <input type="hidden" name="revision_version" value="{$name}">{$name}
        {else}
          <input
            type="text"
            name="revision_version"
            size="10"
            maxlength="10"
            value="{$name}"
            {if $translator}disabled="disabled"{/if}
          />
        {/if}
        </td>
      </tr>
      {if $file_needed}
      <tr>
        <th style="vertical-align: top;">{'File'|@translate}</th>
        <td>
            <div style="margin-bottom: 5px;">
            {if in_array('upload', $upload_methods)}
              <label><input type="radio" name="file_type" value="upload" onClick="javascript:showOnlyThisChild('upload_types', 'upload_type');" {if !$form.file_type || $form.file_type=='upload'}checked{/if}> {'Upload a file'|@translate}</label>
            {/if}
            {if in_array('svn', $upload_methods)}
              <label><input type="radio" name="file_type" value="svn" onClick="javascript:showOnlyThisChild('upload_types', 'svn_type');" {if $form.file_type=='svn'}checked{/if}> {'Use SVN file creation'|@translate}</label>
            {/if}
            {if in_array('url', $upload_methods)}
              <label><input type="radio" name="file_type" value="url" onClick="javascript:showOnlyThisChild('upload_types', 'url_type');" {if $form.file_type=='url'}checked{/if}> {'Download from URL'|@translate}</label>
            </div>
            {/if}

          <div id="upload_types">
          {if in_array('upload', $upload_methods)}
            <div id="upload_type">
              <input type="file" name="revision_file" size="35">
            </div>
          {/if}

          {if in_array('svn', $upload_methods)}
            <div id="svn_type" style="display: none;">
              {'URL'|@translate} <input type="text" name="svn_url" value="{$SVN_URL}" size="65"> &nbsp;
              {'Revision'|@translate} <input type="text" name="svn_revision" value="{$SVN_REVISION}" size="5">
            </div>
          {/if}

          {if in_array('url', $upload_methods)}
            <div id="url_type" style="display: none;">
              {'URL'|@translate} <input type="text" name="download_url" value="{$form.download_url}" size="65">
            </div>
          {/if}
          </div>
        </td>
      </tr>
      {/if}
      <tr>
        <th>{'Compatibility'|@translate}</th>
        <td>
          <div class="checkboxBox">
          <select name="compatible_versions[]" id="compatible_versions" style="width:665px;" data-placeholder="{'Choose compatibility versions...'|@translate}" multiple="multiple">
{foreach from=$versions item=version}
            <option value="{$version.id_version}" {$version.selected}>{$version.name}</option>
{/foreach}
          </select>
          </div>
        </td>
      </tr>
{if $authors|@count > 1}
      <tr>
        <th>{'Author'|@translate}</th>
        <td>
          {foreach from=$authors item=author}
          <label><input type="radio" name="author" value="{$author}" {if $author == $selected_author}checked="checked"{/if} {if $translator}disabled="disabled"{/if}>{$author|@get_author_name}</label>
          {/foreach}
        </td>
      </tr>
{/if}
      <tr>
        <th>{'Description'|@translate}</th>
        <td>
          <select name="lang_desc_select">
          {foreach from=$languages item=language}
            <option value="{$language.id}" id="opt_{$language.id}" {if $default_language == $language.id}selected="selected"{/if}>
              {if empty($descriptions[$language.id])}&#x2718;{else}&#x2714;{/if} &nbsp;{$language.name}</option>
          {/foreach}
          </select>
          {foreach from=$languages item=language}
          <span id="span_{$language.id}" class="desc" style="display: none;"> &nbsp;
            <label><input type="radio" name="default_description" value="{$language.id}" {if $default_language == $language.id}checked="checked"{/if} {if $translator}disabled="disabled"{/if}> {'Default description'|@translate}</label>
            <br>
            <textarea cols="80" rows="10" name="revision_descriptions[{$language.id}]" id="desc_{$language.id}" {if $translator and !$language.id|@in_array:$translator_languages}disabled="disabled"{/if}>{$descriptions[$language.id]}</textarea>
          </span>
          {/foreach}
          <p class="default_description"></p>
        </td>
      </tr>
{if !empty($extensions_languages)}
      <tr>
        <th>{'Available languages'|@translate}</th>
        <td>
          <div class="checkboxBox">
          <select name="extensions_languages[]" id="extensions_languages" style="width:665px;" data-placeholder="{'Choose available languages...'|@translate}" multiple="multiple" {if $translator}disabled="disabled"{/if}>
{foreach from=$extensions_languages item=lang}
            <option value="{$lang.id}" {$lang.selected}>[{$lang.code}] {$lang.name}</option>
{/foreach}
          </select>
          </div>
        </td>
      </tr>
{/if}
{if $use_agreement}
      <tr>
        <th>{'Agreement'|@translate}</th>
        <td>
          <label><input type="checkbox" name="accept_agreement" {$accept_agreement_checked} {if $translator}disabled="disabled"{/if}>{$agreement_description}</label>
        </td>
      </tr>
{/if}
    </table>

    <div>
      <input type="submit" value="{'Submit'|@translate}" name="submit" />
    </div>
  </fieldset>
</form>

{known_script id="jquery" src="template/jquery.min.js"}
{known_script id="jquery.chosen" src="template/chosen.jquery.min.js"}
{html_head}
<link rel="stylesheet" type="text/css" href="template/chosen.css">
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
  $('textarea[name^="revision_descriptions"]').keyup(function () {ldelim}
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
  $('#compatible_versions').chosen();
  $('#extensions_languages').chosen();
});

function set_default_description (id) {ldelim}
  $(".default_description").html("{'Default description'|@translate}: "+languages[id]);
}

$("#span_"+{$default_language}).show();
set_default_description({$default_language});
</script>
