<?php

$routes[ "|^/robots\.txt$|" ] = "xMVC\\Mod\\WireKit\\Robotstxt";
$routes[ "|^/sitemap-([A-Za-z-]+)\.xml$|" ] = "xMVC\\Mod\\WireKit\\Sitemapxml/View/%1";

$lowPriorityRoutes[ "|^/(.*)$|" ] = "xMVC\\Mod\\WireKit\\Processor/Page/%1";

?>