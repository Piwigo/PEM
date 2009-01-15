<h2>Versions</h2>

<div id="version_form" class="changelogRevision">
  <div
    id="version_form_header"
    onclick="revToggleDisplay('version_form_header', 'version_form_content')"
  {if $version_form_expanded}
    class="changelogRevisionHeaderExpanded"
  {else}
    class="changelogRevisionHeaderCollapsed"
  {/if}
  >
  {$version_form_title}
  </div> <!-- version_form_header -->

  <div
    id="version_form_content"
  {if !$version_form_expanded}
      style="display:none"
  {/if}
  >
    <form method="post" action="{$f_action}">
  {if isset($version_id)}
    <input type="hidden" name="id" value="{$version_id}" />
  {/if}
      <table>
        <tr>
          <th>Name</th>
          <td><input type="text" name="name" size="35" maxlength="255" value="{$name}" /></td>
        </tr>
      </table>
      
      <div>
        <input type="submit" value="Submit" name="submit_{$version_form_type}" />
      </div>
    </form>
  </div> <!-- version_form_content -->
</div>

<ul>
{foreach from=$versions item=version}
  <li>
    <a href="versions.php?edit={$version.id}"><img style="border:none;" src="../template/images/admin_edit.png" /></a>
    <a href="versions.php?delete={$version.id}" onclick="return confirm_del();"><img style="border:none;" src="../template/images/admin_delete.png" /></a>
    {$version.name}
  </li>
{/foreach}
</ul>
