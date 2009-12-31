<?php

namespace Module\ContentLAB;

use xMVC\Sys\XMLModelDriver;

class Weather
{
	public function __construct( &$instance, &$view, $definition, $instanceName )
	{
		$city = $instance->xPath->query( "//clab:city" )->item( 0 )->nodeValue;
		$feed = "http://www.google.com/ig/api?weather=" . $city . "";

		$weather = new XMLModelDriver( file_get_contents( $feed ) );

		$view->PushModel( $weather );
	}
}

?>