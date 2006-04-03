    <h1>Vos contributions</h1>
    <div class="nav_left">Extensions {L_EXTENSIONS_START} - {L_EXTENSIONS_END} (total {L_EXTENSIONS_COUNT}) </div>
    <div class="nav_right">{U_PREVIOUS} Page {L_PAGE_ID} sur {L_PAGE_COUNT} {U_NEXT}</div>
    
    <!-- Used to fix a margin bug with IE... -->
    <br />

    <!-- BEGIN extension -->
    <div class="row">
      <strong><a href="view_extension.php?id={L_EXTENSION_ID}">{L_EXTENSION_NAME}</a></strong><br />
      <p>{L_EXTENSION_DESCRIPTION}</p>
      <span class="small"><a href="contributions.php?id={L_EXTENSION_ID}&amp;action=mod_ext">Modifier</a> / 
      <a onclick="return confirm_del();" href="contributions.php?id={L_EXTENSION_ID}&amp;action=del_ext">Supprimer</a></span>
    </div>
    <!-- END extension -->
    
    <div class="nav_left">Extensions {L_EXTENSIONS_START} - {L_EXTENSIONS_END} (total {L_EXTENSIONS_COUNT}) </div>
    <div class="nav_right">{U_PREVIOUS} Page {L_PAGE_ID} sur {L_PAGE_COUNT} {U_NEXT}</div>
    <div style="clear : both;"></div>