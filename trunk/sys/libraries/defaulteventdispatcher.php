<?php

namespace xMVC\Sys;

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
    public function __construct(EventDispatcher $target = NULL)
    {
        $this->target = ( $target == NULL ) ? $this : $target;
        $this->listeners = array();
    }
    
    public function addEventListener(string $eventType, Delegate $delegate)
    {
        if( !$this->hasEventListener( $eventType, $delegate ) )
        {
            $bucket = $this->getOrCreateListenerBucket( $eventType );
            $bucket[] = $delegate;
            $this->listeners[ $eventType ] = $bucket;            
        }
    }
    
    private function hasEventListener(string $eventType, Delegate $delegate)
    {
        if( isset( $this->listeners[ $eventType ] ) )
        {                    
            $bucket = $this->listeners[ $eventType ];        
            foreach( $bucket as $value )
            {
                if( $value->equals( $delegate ) )
                {
                    return( true );
                }
            }
        }
        return( false );
    }
    
    private function getOrCreateListenerBucket(string $eventType)
    {
        if( !isset( $this->listeners[ $eventType ] ) )
        {            
            $this->listeners[ $eventType ] = array();          
        }        
        return( $this->listeners[ $eventType ] );
    }
    
    public function removeEventListener(string $eventType, Delegate $delegate)
    {
        if( isset( $this->listeners[ $eventType ] ) )
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
    
    public function dispatchEvent(Event $event)
    {        
        $eventType = $event->type;
        if( isset( $this->listeners[ $eventType ] ) )
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