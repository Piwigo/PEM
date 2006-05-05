<h2>{EXTENSION_NAME}, revision {REVISION}</h2>

<ul class="actionLinks">
  <li><a href="{U_EXTENSION}">See extension</a></li>
  <!-- BEGIN admin -->
  <li><a href="{U_MODIFY}">Modify revision</a></li>
  <li><a href="{U_DEL}" onclick="return confirm_del();" >Delete revision</a></li>
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
