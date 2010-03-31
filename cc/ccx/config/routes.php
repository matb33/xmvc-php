<?php

$routes[ "/^\/robots\.txt$/" ] = "xMVC\\Mod\\CC\\robotstxt";
$routes[ "/^\/sitemap-([a-z]+)\.xml$/" ] = "xMVC\\Mod\\CC\\sitemapxml/view/%1";
$routes[ "/^\/ccms(.*)/" ] = "xMVC\\Mod\\CC\\ccms-admin%1";

?>