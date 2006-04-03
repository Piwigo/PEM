<h1>Ajouter une extension</h1>
<form method="post" action="extensions.php?action=add" enctype="multipart/form-data">
<table width="60%" border="0">
  <tr>
    <td>Chemin de l'archive :</td>
    <td><input type="file" name="extension_file" size="35" /></td>
  </tr>
  <tr>
    <td>Nom de l'extension :</td>
    <td><input type="text" name="extension_name" size="35" maxlength="255" /></td>
  </tr>
  <tr>
    <td>Version :</td>
    <td><input type="text" name="extension_version" size="10" maxlength="10" /></td>
  </tr>
  <tr>
    <td valign="top">Catégorie :</td>
    <td>
      <select name="extension_category[]" size="5" multiple="true">
      <!-- BEGIN extension_category -->
        <option value="{L_EXTENSION_CAT_VALUE}">{L_EXTENSION_CAT_NAME}</option>
      <!-- END extension_category -->
      </select></td>
  </tr>
  <tr>
    <td valign="top">Compatibilité PWG :</td>
    <td>
      <select name="extension_compatibility[]" multiple>
      <!-- BEGIN extension_compatibility -->
        <option value="{L_EXTENSION_COMP_VALUE}">{L_EXTENSION_COMP_NAME}</option>
      <!-- END extension_compatibility -->
      </select></td>
  </tr>
  <tr>
    <td valign="top">Description :</td>
    <td><textarea cols="40" rows="8" name="extension_description"></textarea></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Envoyer" name="send" /></td>
  </tr>
</table>
</form>