<?php

$routes[ "|^/robots\.txt$|" ] = "xMVC\\Mod\\CC\\Robotstxt";
$routes[ "|^/sitemap-([A-Za-z-]+)\.xml$|" ] = "xMVC\\Mod\\CC\\Sitemapxml/View/%1";

$lowPriorityRoutes[ "|^/(.*)$|" ] = "xMVC\\Mod\\CC\\Processor/Page/%1";

?>