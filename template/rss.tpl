{$xml_header}

<rss version="2.0">
  <channel>
    <title>{$title}</title>
    <link>{$website_url}</link>
    <description>{$description}</description>
    <language>{$language}</language>
    <webmaster>{$webmaster_email}</webmaster>
    
{foreach from=$revisions item=rev}
    <item>
      <title>{$rev.ext_name}, revision {$rev.name}</title>
      <link>{$rev.url}</link>
      <description><![CDATA[
        <p><strong>Version</strong>: {$rev.name}</p>
        <p><strong>Extension description</strong>: {$rev.ext_description|nl2br}</p>
        <p><strong>Revision description</strong>: {$rev.description|nl2br}</p>
      ]]></description>
      <author>{$rev.ext_author}</author>
    </item>
{/foreach}
    
  </channel>
</rss>