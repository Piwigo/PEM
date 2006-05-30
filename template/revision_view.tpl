<h2>{EXTENSION_NAME}, revision {REVISION}</h2>

<ul class="actionLinks">
  <li><a href="{U_EXTENSION}" title="See extension"><img src="template/images/see_extension.png"></a></li>
  <!-- BEGIN admin -->
  <li><a href="{U_MODIFY}" title="Modify revision"><img src="template/images/modify.png"></a></li>
  <li><a href="{U_DELETE}" onclick="return confirm_del();" title="Delete revision"><img src="template/images/delete.png"></a></li>
  <!-- END admin -->
</ul>

<div class="row">
  <ul class="extensionInfos">
    <li>Author: {AUTHOR}</li>
  </ul>
  <blockquote>{EXTENSION_DESCRIPTION}</blockquote>
</div>

<div class="row">
  <a href="{U_DOWNLOAD}" title="Download revision {REVISION}"><img class="download" src="template/images/download.png" /></a>
  <p>Revision: {REVISION}</p>
  <p>Released on: {DATE}</p>
  <p>Compatible with: {VERSIONS_COMPATIBLE}</p>

  <blockquote>
    {REVISION_DESCRIPTION}
  </blockquote>
</div>
