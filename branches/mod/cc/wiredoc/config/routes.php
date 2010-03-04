<?php

$lowPriorityRoutes[ "/^\/robots\.txt$/" ] = "Module\\CC\\robotstxt";
$lowPriorityRoutes[ "/^\/sitemap-([a-z]+)\.xml$/" ] = "Module\\CC\\sitemapxml/view/%1";
$lowPriorityRoutes[ "/^\/ccms(.*)/" ] = "Module\\CC\\ccms-admin%1";
$lowPriorityRoutes[ "/^\/--translate(.*)/" ] = "Module\\CC\\translate/lang%1";

$lowPriorityRoutes[ "/^\/(.*)$/" ] = "Module\\CC\\processor/page/%1";

?>