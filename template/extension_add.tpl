<h1>Ajouter une extension</h1>

<form method="post" action="extensions.php?action=add" enctype="multipart/form-data">
  <fieldset>
    <legend>Extension informations</legend>

    <table>
      <tr>
        <th>Nom de l'extension</th>
        <td><input type="text" name="extension_name" size="35" maxlength="255" /></td>
      </tr>
      <tr>
        <th>Catégorie</th>
        <td>
          <div class="checkboxBox">
            <!-- BEGIN extension_category -->
            <label><input type="checkbox" name="extension_category[]" value="{L_EXTENSION_CAT_VALUE}" /> {L_EXTENSION_CAT_NAME}</label>
            <!-- END extension_category -->
          </div>
        </td>
      </tr>
      <tr>
        <th>Description</th>
        <td><textarea cols="40" rows="8" name="extension_description"></textarea></td>
      </tr>
    </table>

    <div>
      <input type="submit" value="Submit" name="submit" />
    </div>
  </fieldset>
</form>