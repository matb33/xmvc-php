<?php

namespace Modules\WebWiredoc\Libraries\Components;

interface IComponent
{
	public function __construct( $componentClass = null, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 );
}