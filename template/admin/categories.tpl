      <h1>Liste des catégories</h1>
      <!-- BEGIN category -->
      <p style="margin:5px;padding:0;"><span title="{L_CATEGORY_DESCRIPTION}" style="font-weight:bold;"><a href="categories.php?action=mod&amp;id={L_CATEGORY_ID}">{L_CATEGORY_NAME}</a>
      </span> <span class="small">[ <a href="categories.php?action=del&amp;id={L_CATEGORY_ID}" onclick="return confirm_del();">X</a> ]</span></p>
      <!-- BEGIN category_sublevel -->
      <ul>
        <!-- BEGIN category_sublevel_item -->
        <li><a href="categories.php?action=mod&amp;id={L_CATEGORY_SUBLEVEL_ITEM_ID}" title="{L_CATEGORY_SUBLEVEL_DESCRIPTION}">{L_CATEGORY_SUBLEVEL_ITEM}</a> <span class="small">[ <a href="categories.php?action=del&amp;id={L_CATEGORY_SUBLEVEL_ITEM_ID}" onclick="return confirm_del();">X</a> ]</span></li>
        <!-- END category_sublevel_item -->
      </ul>
      <!-- END category_sublevel -->
      <!-- END category -->