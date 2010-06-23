<?php

use System\Libraries\Config;

if( !Config::$data[ "isProduction" ] )
{
	$routes[ "/^\/phpinfo/" ] = "Modules\\Phpinfo\\Controllers\\Phpinfo";
}