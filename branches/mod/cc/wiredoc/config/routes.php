<?php

$lowPriorityRoutes[ "/^\/robots\.txt$/" ] = "xMVC\\Mod\\CC\\robotstxt";
$lowPriorityRoutes[ "/^\/sitemap-([a-z]+)\.xml$/" ] = "xMVC\\Mod\\CC\\sitemapxml/view/%1";
$lowPriorityRoutes[ "/^\/--translate(.*)/" ] = "xMVC\\Mod\\CC\\translate/lang%1";

$lowPriorityRoutes[ "/^\/(.*)$/" ] = "xMVC\\Mod\\CC\\processor/page/%1";

?>