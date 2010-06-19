<?php

$routes[ "|^/robots\.txt$|" ] = "Module\\WebCommon\\Controllers\\Robotstxt";
$routes[ "|^/sitemap-([A-Za-z-]+)\.xml$|" ] = "Module\\WebCommon\\Controllers\\Sitemapxml/View/%1";