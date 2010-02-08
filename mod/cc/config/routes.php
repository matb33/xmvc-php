<?php

$routes[ "/^\/robots\.txt$/" ] = "Module\\CC\\robotstxt";
$routes[ "/^\/sitemap-([a-z]+)\.xml$/" ] = "Module\\CC\\sitemapxml/view/%1";
$routes[ "/^\/ccms(.*)/" ] = "Module\\CC\\ccms-admin%1";

?>