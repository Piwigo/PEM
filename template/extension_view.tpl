<h2>{L_EXTENSION_NAME}</h2>

<ul class="actionLinks">
  <!-- BEGIN show_full_cl -->
  <li><a href="{U_SHOW_FULL_CL}" title="See full ChangeLog"><img src="template/images/show_full_changelog.png" alt="See full ChangeLog"></a></li>
  <!-- END show_full_cl -->

  <!-- BEGIN hide_full_cl -->
  <li><a href="{U_HIDE_FULL_CL}" title="Hide full ChangeLog"><img src="template/images/show_full_changelog.png" alt="Hide full ChangeLog"></a></li>
  <!-- END hide_full_cl -->

  <!-- BEGIN admin -->
  <li><a href="{U_MODIFY}" title="Modify extension"><img src="template/images/modify.png" alt="Modify extension"></a></li>
  <li><a href="{U_DELETE}" onclick="return confirm_del();" title="Delete extension"><img src="template/images/delete.png" alt="Delete extension"></a></li>
  <li><a href="{U_LINKS}" title="Manage links"><img src="template/images/links.png" alt="links"></a></li>
  <li><a href="{U_ADD_REV}" title="Add revision"><img src="template/images/add_revision.png" alt="Add revision"></a></li>
  <!-- END admin -->
</ul>

<ul class="extensionInfos">
  <li>Author: {L_EXTENSION_AUTHOR}</li>
  <li>First revision date: {EXTENSION_FIRST_REVISION_DATE}</li>
  <li>Latest revision date: {EXTENSION_LAST_REVISION_DATE}</li>
</ul>

<p>{L_EXTENSION_DESCRIPTION}</p>

<ul>
  <!-- BEGIN link -->
  <li><strong><a href="{LINK_URL}">{LINK_NAME}</a></strong>: {LINK_DESCRIPTION}</li>
  <!-- END link -->
</ul>

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
