<h1>{L_EXTENSION_NAME}</h1>

<!-- BEGIN admin -->
<div class="nav_right">
  <a href="{U_MODIFY}">Modify extension</a>
  | <a href="contributions.php?action=del_ext&amp;id={L_EXTENSION_ID}" onclick="return confirm_del();" >Delete extension</a>
  | <a href="{U_ADD_REV}">Add revision</a>
</div>
<!-- END admin -->

<span class="small">Par {L_EXTENSION_AUTHOR}</span>
<p>{L_EXTENSION_DESCRIPTION}</p>

<!-- BEGIN switch_no_rev -->
<p>No revision available for this extension. Either because there is no
revision at all or because there is no revision compatible with the verion
filter you set.</p>
<!-- END switch_no_rev -->

<table class="revisions">
  <tr>
    <th>Revision</th>
    <th>Date</th>
    <th>Compatibility</th>
  </tr>
  <!-- BEGIN revision -->
  <tr>
    <td><a href="{U_GOTO}">revision {REVISION}</a></td>
    <td>{DATE}</td>
    <td>{VERSIONS_COMPATIBLE}</td>
  </tr>
  <!-- END revision -->
</table>

<p>
  <!-- BEGIN show_full_cl -->
  <a href="{U_SHOW_FULL_CL}">See full ChangeLog</a>
  <!-- END show_full_cl -->

  <!-- BEGIN hide_full_cl -->
  <a href="{U_HIDE_FULL_CL}">Hide full ChangeLog</a>
  <!-- END hide_full_cl -->
</p>

<!-- BEGIN detailed_revision -->
<div class="row">
  <a href="{U_DOWNLOAD}" title="Download revision {REVISION}"><img class="download" src="template/images/download.png" /></a>
  <p>Revision: {REVISION}</p>
  <p>Released on: {DATE}</p>
  <p>Compatible with: {VERSIONS_COMPATIBLE}</p>

  <blockquote>
    {DESCRIPTION}
  </blockquote>
</div>
<!-- END detailed_revision -->
