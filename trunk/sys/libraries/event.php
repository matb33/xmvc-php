<?php

namespace xMVC\Sys;

/**
 * Class that embodies the concept of an event, where each event has a type, target, and optional arguments.
 * 
 * @author Darren Schnare
 * @copyright 2010
 */
class Event
{    
    public $type = "";
    public $target = NULL;
    public $arguments;   
    
    /**
     * Constructs a new event object with the specified type and option arguments.
     * If the arguments is NULL then the arguments property is set to an empty array.
     */
    public function __construct(string $type, array $args = NULL)
    {
        $this->type = $type;
        $this->arguments = ($args == NULL) ? array() : $args;
    }         
    
    public function __toString()
    {
        return( "type: " . $this->type . "  target: " . $this->target . "  arguments: " . print_r( $this->arguments, true ) );
    }
}

?>