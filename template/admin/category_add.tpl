<h1>Ajout de catégorie</h1>

<form method="post" action="categories.php?action=add">
<table width="60%" border="0">
  <tr>
    <td>Nom de la catégorie :</td>
    <td><input type="text" name="name" size="35" maxlength="255" /></td>
  </tr>
  <tr>
    <td>Description :</td>
    <td><input type="text" name="description" size="35" maxlength="255" /></td>
  </tr>
  <tr>
    <td>Catégorie parente :</td>
    <td>
      <select name="parent">
        <option value="0" selected>&lt; Aucune &gt;</option>
        <!-- BEGIN category -->
        <option value="{L_CATEGORY_ID}">{L_CATEGORY_NAME}</option>
        <!-- END category -->
      </select>
    </td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Envoyer" name="send" /></td>
</table>
</form>