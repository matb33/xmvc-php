<?php

$defaultController = "Module\\CC\\home";
//$fallbackRoute = "Module\\CC\\root/page";

// TO-DO: Move sys load to a reservedRoutes array (consider a routes object with weighting)
$routes[ "/^\/load(.*)/" ] = "xMVC\\Sys\\load%1";	// re-iterate this rule so it takes precedence
//$routes[ "/^\/sitemap\.xml(.*)/" ] = "Module\\CC\\root/sitemap%1";
$routes[ "/^\/(.+)/" ] = "Module\\CC\\%1";

?>
