    <h1>{L_CATEGORY_NAME}</h1>
    <div class="nav_left">Extensions {L_EXTENSIONS_START} - {L_EXTENSIONS_END} (total {L_EXTENSIONS_COUNT}) </div>
    <div class="nav_right">{U_PREVIOUS} Page {L_PAGE_ID} sur {L_PAGE_COUNT} {U_NEXT}</div>
    
    <!-- Used to fix a margin bug with IE... -->
    <br />

    <!-- BEGIN extension -->
    <div class="row">
      <strong><a href="view_extension.php?id={L_EXTENSION_ID}">{L_EXTENSION_NAME} {L_EXTENSION_VERSION}</a></strong><br />
      <span class="small">Par {L_EXTENSION_AUTHOR}<br />Compatibilité {L_EXTENSION_COMPATIBILITY}</span><br />
      <p>{L_EXTENSION_DESCRIPTION}</p>
      
      <!-- BEGIN switch_admin -->
      <span class="small"><a href="contributions.php?action=mod_ext&amp;id={L_EXTENSION_ID}">Modifier</a> /
      <a href="contributions.php?action=del_ext&amp;id={L_EXTENSION_ID}">Supprimer</a>
      <!-- END switch_admin -->
    </div>
    
    <!-- END extension -->
    
    <div class="nav_left">Extensions {L_EXTENSIONS_START} - {L_EXTENSIONS_END} (total {L_EXTENSIONS_COUNT}) </div>
    <div class="nav_right">{U_PREVIOUS} Page {L_PAGE_ID} sur {L_PAGE_COUNT} {U_NEXT}</div>
    
    <div style="clear : both;"></div>