<?php

// NOT CURRENTLY IN USE, BUT SHOULD MIGRATE WIREKIT CLASS TO SOMETHING LIKE THIS

namespace xMVC\Mod\WireKit;

use xMVC\Sys\Events\DefaultEventDispatcher;
use xMVC\Sys\Delegate;

class Component extends DefaultEventDispatcher
{
	private $component;
	private $instanceName;
	private $eventName;
	private $delegate;
	private $parameters;
	private $defaultDelegateMethod = "OnComponentReadyForProcessing";

	public function __construct( $component )
	{
		$this->component = $component;
	}

	public function SetEventName( $eventName )
	{
		$this->eventName = $eventName;
	}

	public function SetInstanceName( $instanceName )
	{
		$this->instanceName = $instanceName;
	}

	public function SetDispatchScope( $dispatchScope )
	{
		$this->delegate = new Delegate( $this->defaultDelegateMethod, $dispatchScope );
	}

	public function SetDelegate( Delegate $delegate )
	{
		$this->delegate = $delegate;
	}

	public function AddParameter( $parameter )
	{
		$this->parameters[] = $parameter;
	}
}

?>