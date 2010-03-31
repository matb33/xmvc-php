<?php

$routes[ "/^\/robots\.txt$/" ] = "xMVC\\Mod\\CC\\robotstxt";
$routes[ "/^\/sitemap-([A-Za-z-]+)\.xml$/" ] = "xMVC\\Mod\\CC\\sitemapxml/view/%1";
$routes[ "/^\/--translate(.*)/" ] = "xMVC\\Mod\\CC\\translate/lang%1";

$lowPriorityRoutes[ "/^\/(.*)$/" ] = "xMVC\\Mod\\CC\\processor/page/%1";

?>