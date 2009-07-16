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
        <th>{'Notes'|@translate}</th>
        <td>
          <textarea cols="80" rows="10" name="revision_changelog">{$description}</textarea>
        </td>
      </tr>
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