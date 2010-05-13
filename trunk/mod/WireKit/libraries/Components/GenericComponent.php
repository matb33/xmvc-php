<?php

namespace xMVC\Mod\WireKit\Components;

use xMVC\Mod\WireKit\Components\Component;

class GenericComponent extends Component
{
	public function __construct( $originalComponentClass, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 )
	{
		parent::__construct( $originalComponentClass, $instanceName, $eventName, $parameters, $cacheMinutes );
	}
}

?>