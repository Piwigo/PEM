<h2>Tags</h2>

<div id="tag_form" class="changelogRevision">
  <div
    id="tag_form_header"
    onclick="revToggleDisplay('tag_form_header', 'tag_form_content')"
  {if isset($tag_form_expanded) and $tag_form_expanded}
    class="changelogRevisionHeaderExpanded"
  {else}
    class="changelogRevisionHeaderCollapsed"
  {/if}
  >
  {$tag_form_title}
  </div> <!-- tag_form_header -->

  <div
    id="tag_form_content"
  {if !isset($tag_form_expanded) or !$tag_form_expanded}
      style="display:none"
  {/if}
  >
    <form method="post" action="{$f_action}">
  {if isset($tag_id)}
    <input type="hidden" name="id" value="{$tag_id}" />
  {/if}
      <table style="text-align: center;">
        <tr>
          <th>Language</th>
          <th>Name</th>
          <th>Default</th>
        </tr>
        {foreach from=$languages item=language}
        <tr>
          <td>{$language.name}</td>
          <td><input type="text" name="name[{$language.id}]" size="35" maxlength="255" value="{if isset($name[$language.id])}{$name[$language.id]}{/if}" /></td>
          <td><input type="radio" name="default_name" value="{$language.id}" {if $default_name == $language.id}checked="checked"{/if}></td>
        </tr>
        {/foreach}
      </table>
      
      <div>
        <input type="submit" value="Submit" name="submit_{$tag_form_type}" />
      </div>
    </form>
  </div> <!-- tag_form_content -->
</div>

<ul>
{foreach from=$tags item=tag}
  <li>
    <a href="tags.php?edit={$tag.id}"><img style="border:none;" src="../template/images/admin_edit.png" /></a>
    <a href="tags.php?delete={$tag.id}" onclick="return confirm_del();"><img style="border:none;" src="../template/images/admin_delete.png" /></a>
    {$tag.name}
  </li>
{/foreach}
</ul>
