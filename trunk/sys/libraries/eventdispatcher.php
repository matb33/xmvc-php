<?php

namespace xMVC\Sys;

/**
 * Represents the interface for all objects that can dispatch events.
 * 
 * @author Darren Schnare
 * @copyright 2010
 */
 interface EventDispatcher
 {
    /**
     * Adds an event listener for the specified event type to this event dispatcher.
     * Calling this multiple times with the same arguments will only result in one listener being added.
     */
    public function addEventListener(string $eventType, Delegate $delegate);
    
    /**
     * Removes an event listener for the specified event type from this event dispatcher.
     */
    public function removeEventListener(string $eventType, Delegate $delegate);    
    
    /**
     * Removes all event listeners listening to this event dispatcher.
     */
    public function removeAllEventListeners();
    
    /**
     * Dispatches an event on this event dispatcher.
     * The Event object must have its type set to the type listeners have been added to.
     */
    public function dispatchEvent(Event $event);
 }
 
 ?>