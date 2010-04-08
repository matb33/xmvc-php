<?php

use xMVC\Sys\Config;

if( !Config::$data[ "isProduction" ] )
{
	$routes[ "/^\/phpinfo/" ] = "xMVC\\Mod\\Phpinfo\\Phpinfo";
}

?>