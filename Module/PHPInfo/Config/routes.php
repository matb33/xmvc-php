<?php

use System\Libraries\Config;

if( !Config::$data[ "isProduction" ] )
{
	$routes[ "/^\/phpinfo/" ] = "Module\\Phpinfo\\Controllers\\Phpinfo";
}