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
          <input
            type="text"
            name="revision_version"
            size="10"
            maxlength="10"
            value="{$name}"
          />
        </td>
      </tr>
      {if $file_needed}
      <tr>
        <th style="vertical-align: top;">{'File'|@translate}</th>
        <td>
          {if isset($allow_svn_file_creation)}
            <div style="margin-bottom: 5px;">
              <input type="radio" name="file_type" value="upload" onClick="javascript: toggleDisplay('upload_type'); toggleDisplay('svn_type');" checked="checked"> {'Upload a file'|@translate}
              <input type="radio" name="file_type" value="svn" onClick="javascript: toggleDisplay('upload_type'); toggleDisplay('svn_type');"> {'Use SVN file creation'|@translate}
            </div>
            <div id="upload_type">
              <input type="file" name="revision_file" size="35" />
            </div>
            <div id="svn_type" style="display: none;">
              {'URL'|@translate} <input type="text" name="svn_url" value="{$SVN_URL}" size="65"> &nbsp;
              {'Revision'|@translate} <input type="text" name="svn_revision" value="HEAD" size="5">
            </div>
          {else}
            <input type="file" name="revision_file" size="35" />
          {/if}
        </td>
      </tr>
      {/if}
      <tr>
        <th>{'Compatibility'|@translate}</th>
        <td>
          <div class="checkboxBox">
{foreach from=$versions item=version}
            <label>
              <input type="checkbox" name="compatible_versions[]" value="{$version.id_version}" {$version.checked} />{$version.name}
            </label>
{/foreach}
          </div>
        </td>
      </tr>
{if $authors|@count > 1}
      <tr>
        <th>{'Author'|@translate}</th>
        <td>
          {html_radios name="author" values=$authors output=$authors|@get_author_name selected=$selected_author}
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
            <label><input type="radio" name="default_description" value="{$language.id}" {if $default_language == $language.id}checked="checked"{/if}> {'Default description'|@translate}</label>
            <br>
            <textarea cols="80" rows="10" name="revision_descriptions[{$language.id}]" id="desc_{$language.id}">{$descriptions[$language.id]}</textarea>
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
            {foreach from=$extensions_languages item=lang}
            <label><input type="checkbox" name="extensions_languages[]" value="{$lang.id}" title="{$lang.name}" {$lang.checked} />
              <img src="language/{$lang.code}/icon.jpg" alt="{$lang.name}" title="{$lang.name}">&nbsp;</label>
            {/foreach}
          </div>
        </td>
      </tr>
{/if}
{if $use_agreement}
      <tr>
        <th>{'Agreement'|@translate}</th>
        <td>
          <label><input type="checkbox" name="accept_agreement" {$accept_agreement_checked}>{$agreement_description}</label>
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
  $('textarea[name^="revision_descriptions"]').keyup(function () {ldelim}
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