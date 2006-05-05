<h1>Ajouter une extension</h1>

<form method="post" action="{F_ACTION}" enctype="multipart/form-data">
  <fieldset>
    <legend>Extension informations</legend>

    <table>
      <tr>
        <th>Name</th>
        <td><input type="text" name="extension_name" size="35" maxlength="255" value="{NAME}" /></td>
      </tr>
      <tr>
        <th>Categories</th>
        <td>
          <div class="checkboxBox">
            <!-- BEGIN extension_category -->
            <label><input type="checkbox" name="extension_category[]" value="{L_EXTENSION_CAT_VALUE}" {CHECKED} /> {L_EXTENSION_CAT_NAME}</label>
            <!-- END extension_category -->
          </div>
        </td>
      </tr>
      <tr>
        <th>Description</th>
        <td><textarea cols="80" rows="10" name="extension_description">{DESCRIPTION}</textarea></td>
      </tr>
    </table>

    <div>
      <input type="submit" value="Submit" name="submit" />
    </div>
  </fieldset>
</form>