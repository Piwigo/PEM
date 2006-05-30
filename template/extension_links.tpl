<h2>{EXTENSION_NAME}</h2>

<h3>Manage extension links</h3>

<ul class="actionLinks">
  <li><a href="{U_EXTENSION}" title="See extension"><img src="template/images/see_extension.png"></a></li>
</ul>

<form method="post" action="{F_ACTION}" enctype="multipart/form-data">
  <fieldset>
    <legend>Add a link</legend>

    <table>
      <tr>
        <th><strong>Name *</strong></th>
        <td>
          <input
            type="text"
            name="link_name"
            maxlength="50"
            value="{F_LINK_NAME}"
          />
        </td>
      </tr>
      <tr>
        <th><strong>URL *</strong></th>
        <td>
          <input
            type="text"
            name="link_url"
            size="50"
            maxlength="255"
            value="{F_LINK_URL}"
          />
        </td>
      </tr>
      <tr>
      </tr>
      <tr>
        <th>Description</th>
        <td>
          <textarea cols="80" rows="3" name="link_description">{F_LINK_DESCRIPTION}</textarea>
        </td>
      </tr>
    </table>

    <div>
      <input type="submit" value="Submit" name="submit_add" />
    </div>
  </fieldset>
</form>

<form method="post" action="{F_ACTION}" enctype="multipart/form-data">
  <fieldset>
    <legend>Manage links</legend>

    <ul class="linkManagement">
      <!-- BEGIN link -->
      <li>
        <a href="{LINK_U_DELETE}" onclick="return confirm_del();" class="linkAction"><img src="template/images/delete.png"></a>
        <p>
          <strong><a href="{LINK_URL}">{LINK_NAME}</a></strong>
          {LINK_DESCRIPTION}
        </p>
        <p>
          <label>
            Rank:
            <input type="text" name="linkRank[{LINK_ID}]" value="{LINK_RANK}" size="4"/>
          </label>
        </p>
      </li>
      <!-- END link -->
    </ul>

    <div>
      <input type="submit" value="Submit" name="submit_order" />
    </div>
  </fieldset>
</form>