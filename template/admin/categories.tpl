<h2>Categories</h2>

<div id="category_form" class="changelogRevision">
  <div
    id="category_form_header"
    onclick="revToggleDisplay('category_form_header', 'category_form_content')"
  {if $category_form_expanded}
    class="changelogRevisionHeaderExpanded"
  {else}
    class="changelogRevisionHeaderCollapsed"
  {/if}
  >
  {$category_form_title}
  </div> <!-- category_form_header -->

  <div
    id="category_form_content"
  {if !$category_form_expanded}
      style="display:none"
  {/if}
  >
    <form method="post" action="{$f_action}">
  {if isset($category_id)}
    <input type="hidden" name="id" value="{$category_id}" />
  {/if}
      <table>
        <tr>
          <th>Name</th>
          <td><input type="text" name="name" size="35" maxlength="255" value="{$name}" /></td>
        </tr>
      </table>
      
      <div>
        <input type="submit" value="Submit" name="submit_{$category_form_type}" />
      </div>
    </form>
  </div> <!-- category_form_content -->
</div>

<ul>
{foreach from=$categories item=category}
  <li>
    <a href="categories.php?edit={$category.id}"><img style="border:none;" src="../template/images/admin_edit.png" /></a>
    <a href="categories.php?delete={$category.id}" onclick="return confirm_del();"><img style="border:none;" src="../template/images/admin_delete.png" /></a>
    {$category.name}
  </li>
{/foreach}
</ul>
