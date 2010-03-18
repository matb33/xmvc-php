<?php

namespace Module\CC;

use xMVC\Sys\Events\DefaultEventDispatcher;
use xMVC\Sys\Events\Event;
use xMVC\Sys\Delegate;

class EventHelpers
{
	public function __construct()
	{
	}

	protected function Listen( $eventName, Delegate $delegate )
	{
		CC::GetEventPump()->addEventListener( $eventName, $delegate );
	}

	protected function Talk( $sourceModel, Event &$event )
	{
		CC::GetEventPump()->dispatchEvent( new Event( "onComponentBuildComplete", array( "sourceModel" => $sourceModel, "data" => $event->arguments ) ) );
	}
}

?>