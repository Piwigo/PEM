<h2>{'Add/Modify an extension'|translate}</h2>

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'Extension informations'|translate}</legend>

    <table>
      <tr>
        <th>{'Name'|translate}</th>
        <td><input type="text" name="extension_name" size="35" maxlength="255" value="{$extension_name}" /></td>
      </tr>
      <tr>
        <th>{'Categories'|translate}</th>
        <td>
          <div class="checkboxBox">
{foreach from=$extension_categories item=cat}
            <label><input type="checkbox" name="extension_category[]" value="{$cat.value}" {$cat.checked} /> {$cat.name}</label>
{/foreach}
          </div>
        </td>
      </tr>
      <tr>
        <th>{'Description'|translate}</th>
        <td><textarea cols="80" rows="10" name="extension_description">{$extension_description}</textarea></td>
      </tr>
    </table>

    <div>
      <input type="submit" value="{'Submit'|translate}" name="submit" />
    </div>
  </fieldset>
</form>