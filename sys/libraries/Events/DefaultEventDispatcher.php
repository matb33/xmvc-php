<?php

namespace xMVC\Sys\Events;

use xMVC\Sys\Delegate;

/**
 * The default implementation of an event dispatcher.
 *
 * @author Darren Schnare
 * @copyright 2010
 */
class DefaultEventDispatcher implements EventDispatcher
{
    private $target;
    private $listeners;

    /**
     * Constructs a new DefaultEventDispatcher with the optional event target.
     * The target can be used to change the event target when dispatching events using
     * this event dispatcher. This is useful for classes that cannot extend from DefaultEventDispatcher
     * and can only implement the EventDispatcher interface.
     */
    public function __construct( EventDispatcher $target = NULL )
    {
        $this->target = ( $target == NULL ) ? $this : $target;
        $this->listeners = array();
    }

    public function addEventListener( $eventType, Delegate $delegate )
    {
        if( $this->eventListenerNotAdded( $eventType, $delegate ) )
        {
            $this->tryToCreateListenerBucket( $eventType );
            $bucket = &$this->listeners[ $eventType ];
            $bucket[] = $delegate;
        }
    }

    private function eventListenerNotAdded( $eventType, Delegate $delegate )
    {
        if( $this->bucketExists( $eventType ) )
        {
            $bucket = $this->listeners[ $eventType ];

            foreach( $bucket as $value )
            {
                if( $value->equals( $delegate ) )
                {
                    return( false );
                }
            }
        }

        return( true );
    }

    private function bucketExists( $eventType )
    {
        return( isset( $this->listeners[ $eventType ] ) );
    }

    private function tryToCreateListenerBucket( $eventType )
    {
        if( $this->buckDoesNotExist( $eventType ) )
        {
            $this->listeners[ $eventType ] = array();
        }
    }

    private function buckDoesNotExist( $eventType )
    {
        return( !$this->bucketExists( $eventType ) );
    }

    public function removeEventListener( $eventType, Delegate $delegate )
    {
        if( $this->bucketExists( $eventType ) )
        {
            $bucket = &$this->listeners[ $eventType ];

            foreach( $bucket as $key => $value )
            {
                if( $value->equals( $delegate ) )
                {
                    unset( $bucket[ $key ] );
                    break;
                }
            }
        }
    }

    public function removeAllEventListeners()
    {
        foreach( $this->listeners as $key => $bucket )
        {
            unset( $this->listeners[ $key ] );
        }
    }

    public function dispatchEvent( Event $event )
    {
        $eventType = $event->type;

        if( $this->bucketExists( $eventType ) )
        {
            $event->target = $this->target;
            $bucket = $this->listeners[ $eventType ];

            foreach( $bucket as $delegate )
            {
                $delegate->call( $event );
            }
        }
    }
}

?>