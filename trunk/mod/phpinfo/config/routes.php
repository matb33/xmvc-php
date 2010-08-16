<?php

use xMVC\Sys\Config;

if( isset( Config::$data[ "isProduction" ] ) && !Config::$data[ "isProduction" ] )
{
	$routes[ "/^\/phpinfo/" ] = "xMVC\\Mod\\Phpinfo\\Phpinfo";
}