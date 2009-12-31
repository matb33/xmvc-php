<?php

namespace Module\ContentLAB;

class Error404
{
	public function __construct( &$instance, &$view, $definition, $instanceName )
	{
		header( "HTTP/1.1 404 Not Found" );
	}
}

?>