<?php

namespace xMVC\Mod\WiredocPHP\Components;

use xMVC\Mod\WiredocPHP\Components\Component;
use xMVC\Mod\WiredocPHP\Components\IComponent;

class GenericComponent extends Component implements IComponent
{
	public function __construct( $componentClass = null, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 )
	{
		parent::__construct( is_null( $componentClass ) ? __CLASS__ : $componentClass, $instanceName, $eventName, $parameters, $cacheMinutes );
	}
}