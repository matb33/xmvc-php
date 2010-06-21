<?php

$routes[ "|^/robots\.txt$|" ] = "Modules\\WebCommon\\Controllers\\Robotstxt";
$routes[ "|^/sitemap-([A-Za-z-]+)\.xml$|" ] = "Modules\\WebCommon\\Controllers\\Sitemapxml/view/%1";