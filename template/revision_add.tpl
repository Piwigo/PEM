<h1>Ajouter une révision</h1>
<form method="post" action="revisions.php?action=add" enctype="multipart/form-data">
<table width="60%" border="0">
  <tr>
    <td>Chemin de l'archive :</td>
    <td><input type="file" name="revision_file" size="35" /></td>
  </tr>
  <tr>
    <td>Extension :</td>
    <td>
      <select name="revision_extension">
        <!-- BEGIN revision_extension -->
        <option value="{L_REVISION_EXTENSION_VALUE}" >{L_REVISION_EXTENSION_NAME}</option>
        <!-- END revision_extension -->
      </select>
    </td>
  </tr>
  <tr>
    <td>Version :</td>
    <td><input type="text" name="revision_version" size="10" maxlength="10" /></td>
  </tr>
  <tr>
    <td valign="top">Compatibilité PWG :</td>
    <td>
      <select name="revision_compatibility[]" multiple>
      <!-- BEGIN revision_compatibility -->
        <option value="{L_REVISION_COMP_VALUE}" >{L_REVISION_COMP_NAME}</option>
      <!-- END revision_compatibility -->
      </select></td>
  </tr>
  <tr>
    <td valign="top">Changelog :</td>
    <td><textarea cols="40" rows="8" name="revision_changelog"></textarea></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Envoyer" name="send" /></td>
  </tr>
</table>
</form>