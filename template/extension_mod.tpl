<h1>Modifier une extension</h1>
<form method="post" action="contributions.php?action=mod_ext">
<table width="60%" border="0">
  <tr>
    <td>Nom de l'extension :</td>
    <td><input type="text" name="extension_name" size="35" maxlength="255" value="{L_EXTENSION_NAME}" />
        <input type="hidden" name="extension_id" value="{L_EXTENSION_ID}" /></td>
  </tr>
  <tr>
    <td valign="top">Catégorie :</td>
    <td>
      <select name="extension_category[]" size="5" multiple="true">
      <!-- BEGIN extension_category -->
        <option value="{L_EXTENSION_CAT_VALUE}" {L_EXTENSION_CAT_SELECTED}>{L_EXTENSION_CAT_NAME}</option>
      <!-- END extension_category -->
      </select></td>
  </tr>
  <tr>
    <td valign="top">Description :</td>
    <td><textarea cols="40" rows="8" name="extension_description">{L_EXTENSION_DESCRIPTION}</textarea></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Envoyer" name="send" /></td>
  </tr>
</table>
</form>
