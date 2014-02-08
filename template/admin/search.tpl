<h2>Search</h2>

<form action="{$f_action}" method="POST">
  <fieldset>
    <legend><b>Compatibility check</b></legend>
    
    Get extensions incompatible with
    <select name="version0">
      {foreach from=$versions item=version name=v0}
      <option value="{$version.id}" {if (isset($version0) and $version0==$version.id) or (!isset($version0) and $smarty.foreach.v0.index==0)}selected{/if}>{$version.name}</option>
      {/foreach}
    </select>
    
    <input type="submit" name="compatibility_check" value="Search">
    
    {if isset($extensions)}
    <ul>
      {foreach from=$extensions item=extension}
      <li><a href="../extension_view.php?eid={$extension.id_extension}">{$extension.name}</a></li>
      {foreachelse}
      <li><i>No result</i></li>
      {/foreach}
    </ul>
    {/if}
    
    <hr>
    
    Get extensions compatible with
    <select name="version1">
      {foreach from=$versions item=version name=v1}
      <option value="{$version.id}" {if (isset($version1) and $version1==$version.id) or (!isset($version1) and $smarty.foreach.v1.index==1)}selected{/if}>{$version.name}</option>
      {/foreach}
    </select>
    
    and incompatible with
    <select name="version2">
      {foreach from=$versions item=version name=v2}
      <option value="{$version.id}" {if (isset($version2) and $version2==$version.id) or (!isset($version2) and $smarty.foreach.v2.index==0)}selected{/if}>{$version.name}</option>
      {/foreach}
    </select>
    
    <input type="submit" name="inter_compatibility_check" value="Search">
    
    {if isset($inter_extensions)}
    <ul>
      {foreach from=$inter_extensions item=extension}
      <li><a href="../extension_view.php?eid={$extension.id_extension}">{$extension.name}</a></li>
      {foreachelse}
      <li><i>No result</i></li>
      {/foreach}
    </ul>
    {/if}
  </fieldset>
</form>
