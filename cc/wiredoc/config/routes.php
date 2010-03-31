<?php

$lowPriorityRoutes[ "/^\/robots\.txt$/" ] = "xMVC\\Mod\\CC\\robotstxt";
$lowPriorityRoutes[ "/^\/sitemap-([A-Za-z-]+)\.xml$/" ] = "xMVC\\Mod\\CC\\sitemapxml/view/%1";
$lowPriorityRoutes[ "/^\/image\/(.+)\/(.*)$/" ] = "xMVC\\Mod\\CC\\image/%1/%2";
$lowPriorityRoutes[ "/^\/--translate(.*)/" ] = "xMVC\\Mod\\CC\\translate/lang%1";

$lowPriorityRoutes[ "/^\/(.*)$/" ] = "xMVC\\Mod\\CC\\processor/page/%1";

?>