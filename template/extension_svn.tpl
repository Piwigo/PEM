<h2>{$extension_name}</h2>

<h3>{'SVN configuration'|@translate}</h3>

<ul class="actionLinks">
  <li><a href="{$u_extension}" title="{'See extension'|@translate}"><img src="template/images/see_extension.png" alt="{'See extension'|@translate}"></a></li>
</ul>

<form method="post" action="{$f_action}" enctype="multipart/form-data">
  <fieldset>
    <legend>{'SVN configuration'|@translate}</legend>
      <p>{'Repository URL'|@translate} <input type="text" name="url" value="{$SVN_URL}" size="70"/><br><br>
      {if isset($ROOT_DIR)}{'Archive root directory'|@translate} <input type="text" name="root_dir" value="{$ROOT_DIR}" size="30"/><br><br>{/if}
      {if isset($ARCHIVE_NAME)}{'Archive name'|@translate} <input type="text" name="archive_name" value="{$ARCHIVE_NAME}" size="30"/> <i>({'% will be replaced by version number'|@translate})</i><br><br>{/if}
      <input type="submit" value="{'Submit'|@translate}" name="submit" />
      {if isset($SVN_INFOS)}<input type="submit" value="{'Delete SVN data'|@translate}" name="delete" onclick="return confirm('{'Are you sure you want to remove SVN data?'|@translate|escape:javascript}');"/>{/if}
      </p>
  </fieldset>
</form>

{if isset($SVN_INFOS)}
<br>
  <fieldset>
    <legend>{'SVN informations'|@translate}</legend>
      <p>{$SVN_INFOS|@implode:'<br>'}</p>
  </fieldset>
{/if}