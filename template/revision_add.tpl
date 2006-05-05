<h1>{EXTENSION_NAME}</h1>

</h1>

<form method="post" action="{F_ACTION}" enctype="multipart/form-data">
  <fieldset>
    <legend>Add a revision</legend>

    <table>
      <tr>
        <th>Version</th>
        <td><input type="text" name="revision_version" size="10" maxlength="10" /></td>
      </tr>
      <tr>
        <th>Chemin de l'archive</th>
        <td><input type="file" name="revision_file" size="35" /></td>
      </tr>
      <tr>
        <th>Compatibility</th>
        <td>
          <div class="checkboxBox">
          <!-- BEGIN revision_compatibility -->
            <label><input type="checkbox" name="revision_compatibility[]" value="{L_REVISION_COMP_VALUE}" /> {L_REVISION_COMP_NAME}</label>
          <!-- END revision_compatibility -->
          </div>
        </td>
      </tr>
      <tr>
        <th>Notes de version</th>
        <td><textarea cols="80" rows="10" name="revision_changelog"></textarea></td>
      </tr>
    </table>

    <div>
      <input type="submit" value="Submit" name="submit" />
    </div>
  </fieldset>
</form>