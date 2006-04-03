<h1>Modification de catégorie</h1>

<form method="post" action="categories.php?action=mod&amp;id={L_CATEGORY_ID}">
<table width="60%" border="0">
  <tr>
    <td>Nom de la catégorie :</td>
    <td><input type="text" name="name" size="35" maxlength="255" value="{L_CATEGORY_NAME}" /></td>
  </tr>
  <tr>
    <td>Description :</td>
    <td><input type="text" name="description" size="35" maxlength="255" value="{L_CATEGORY_DESCRIPTION}" /></td>
  </tr>
  <tr>
    <td>Catégorie parente :</td>
    <td>
      <select name="parent">
        <option value="0">&lt; Aucune &gt;</option>
        <!-- BEGIN category -->
        <option value="{L_LIST_CATEGORY_ID}" {L_LIST_CATEGORY_SELECTED}>{L_LIST_CATEGORY_NAME}</option>
        <!-- END category -->
      </select>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Envoyer" name="send" /></td>
</table>
</form>