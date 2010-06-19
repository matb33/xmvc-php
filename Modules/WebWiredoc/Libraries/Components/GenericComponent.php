<?php

namespace Modules\WebWiredoc\Libraries\Components;

use Modules\WebWiredoc\Libraries\Components\Component;
use Modules\WebWiredoc\Libraries\Components\IComponent;

class GenericComponent extends Component implements IComponent
{
	public function __construct( $componentClass = null, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 )
	{
		parent::__construct( is_null( $componentClass ) ? __CLASS__ : $componentClass, $instanceName, $eventName, $parameters, $cacheMinutes );
	}
}