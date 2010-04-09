<?php

$routes[ "/^\/robots\.txt$/" ] = "xMVC\\Mod\\CC\\Robotstxt";
$routes[ "/^\/sitemap-([a-z]+)\.xml$/" ] = "xMVC\\Mod\\CC\\Sitemapxml/View/%1";
$routes[ "/^\/ccms(.*)/" ] = "xMVC\\Mod\\CC\\ccms-admin%1";

?>