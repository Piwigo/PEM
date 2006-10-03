<h2>{EXTENSION_NAME}</h2>

<h3>Manage extension screenshot</h3>

<ul class="actionLinks">
  <li><a href="{U_EXTENSION}" title="See extension"><img src="template/images/see_extension.png"></a></li>
</ul>

<form method="post" action="{F_ACTION}" enctype="multipart/form-data">
  <fieldset>
    <legend>Add or replace screenshot</legend>

    <table>
      <tr>
        <th><strong>File *</strong></th>
        <td>
          <input
            type="file"
            name="picture"
            maxlength="50"
            value="{F_PICTURE_NAME}"
          />
        </td>
      </tr>
    </table>

    <div>
      <input type="submit" value="Submit" name="submit_add" />
    </div>
  </fieldset>
</form>

<!-- BEGIN delete -->
<form method="post" action="{F_ACTION}" enctype="multipart/form-data">
  <fieldset>
    <legend>Delete screenshot</legend>

    <table>
      <tr>
        <td>
          <a class="screenshot" href="{U_SCREENSHOT}"><img src="{THUMBNAIL_SRC}"/></a>
        </td>
        <td valign="middle">
          <input type="submit" value="Submit" name="submit_delete" />
        </td>
      </tr>
    </table>
  </fieldset>
</form>
<!-- END delete -->