<h1>{EXTENSION_NAME}</h1>

<form method="post" action="{F_ACTION}" enctype="multipart/form-data">
  <fieldset>
    <legend>Add a revision</legend>

    <table>
      <tr>
        <th>Version</th>
        <td>
          <input
            type="text"
            name="revision_version"
            size="10"
            maxlength="10"
            value="{REVISION_VERSION}"
          />
        </td>
      </tr>
      <tr>
        <th>File</th>
        <td>
          <input type="file" name="revision_file" size="35" />
        </td>
      </tr>
      <tr>
        <th>Compatibility</th>
        <td>
          <div class="checkboxBox">
          <!-- BEGIN compatible_version -->
            <label>
              <input type="checkbox" name="compatible_versions[]" value="{VALUE}" {CHECKED} />{NAME}
            </label>
          <!-- END compatible_version -->
          </div>
        </td>
      </tr>
      <tr>
        <th>Notes</th>
        <td>
          <textarea cols="80" rows="10" name="revision_changelog">{REVISION_DESCRIPTION}</textarea>
        </td>
      </tr>
    </table>

    <div>
      <input type="submit" value="Submit" name="submit" />
    </div>
  </fieldset>
</form>