<?php

$defaultController = "Module\\CC\\home";
//$fallbackRoute = "Module\\CC\\root/page";

//$routes[ "/^\/sitemap\.xml(.*)/" ] = "Module\\CC\\root/sitemap%1";
$routes[ "/^\/(.+)/" ] = "Module\\CC\\%1";

?>
