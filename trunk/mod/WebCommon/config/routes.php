<?php

$routes[ "|^/robots\.txt$|" ] = "xMVC\\Mod\\WebCommon\\Robotstxt";
$routes[ "|^/sitemap-([A-Za-z-]+)\.xml$|" ] = "xMVC\\Mod\\WebCommon\\Sitemapxml/View/%1";