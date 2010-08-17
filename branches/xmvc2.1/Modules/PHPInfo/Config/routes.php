<?php

use System\Libraries\Config;

if( isset( Config::$data[ "isProduction" ] ) && !Config::$data[ "isProduction" ] )
{
	$routes[ "/^\/phpinfo/" ] = "Modules\\Phpinfo\\Controllers\\Phpinfo";
}