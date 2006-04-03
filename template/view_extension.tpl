    <h1>{L_EXTENSION_NAME}</h1>
    <span class="small">Par {L_EXTENSION_AUTHOR}</span>
    <p>{L_EXTENSION_DESCRIPTION}</p>
    <!-- BEGIN switch_admin -->
    <p><span class="small"><a href="contributions.php?action=mod_ext&amp;id={L_EXTENSION_ID}">Modifier</a> / 
    <a href="contributions.php?action=del_ext&amp;id={L_EXTENSION_ID}" onclick="return confirm_del();" >Supprimer</a></span></p>
    <!-- END switch_admin -->
    
    <div class="row">
    <strong>Téléchargement</strong>
      <ul style="line-height : 150%;">
      <!-- BEGIN revision -->
      <li>
        PhpWebGallery {L_REVISION_COMPATIBILITY} : <a href="{U_REVISION_DOWNLOAD}">{L_REVISION_VERSION}</a>
        <span class="small"><a href="javascript:display_changelog({L_REVISION_ID});">( Changelog )</a></span>
      </li>
      <!-- END revision -->
      </ul>
      
      <!-- BEGIN switch_no_rev -->
      Aucune révision disponible pour la version de PhpWebGallery choisie.<br />
      Veuillez changer de filtre de version et réessayer.
      <!-- END switch_no_rev -->
    </div>
    
    <!-- BEGIN revision_changelog -->
    <div class="row" style="display : none;" id="changelog_{L_REVISION_ID}">
    <strong>Changelog de la version {L_CHANGELOG_REVISION_VERSION}</strong>
    <p>{L_REVISION_CHANGELOG}</p>
    </div>
    <!-- END revision_changelog -->