<?php

use System\Libraries\Config;

if( !Config::$data[ "isProduction" ] )
{
	$routes[ "/^\/phpinfo/" ] = "xMVC\\Mod\\Phpinfo\\Phpinfo";
}