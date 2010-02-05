<?php

namespace xMVC\Sys;

/**
 * Class that wraps a PHP callback and treats it as standard function delegate.
 * 
 * @author Darren Schnare
 * @copyright 2010
 */
class Delegate
{
    private $thisObject = NULL;
    private $functionName = "";
    
    /**
     * Constructs a new delegate with the specified functionName and this object.     
     * If functionName is a valid PHP callback then thisObject must be null.
     * 
     * @param functionName The name of a function or for advanced usage, any valid PHP callback that can be passed to call_user_func.
     * @param thisObject The instance the specified function is a member of.     
     * @see http://ca3.php.net/manual/en/function.call-user-func.php
     */
    public function __construct( $functionName, $thisObject = NULL )
    {        
        $this->functionName = $functionName;
        $this->thisObject = $thisObject;
    }
    
    /**
     * Attempts to call the delegate. If the callback encapsulated by this delegate 
     * is not callback then the delegate will not be called. Accepts any number of arguments.
     */
    public function call()
    {                
        $callback = $this->asCallback();                   
        return( $this->callCallback( $callback, func_get_args() ) );   
    }        
    
    /**
     * Converts the delegate to a PHP callback which can be used with call_user_func and call_user_func_array.
     */
    public function asCallback()
    {        
        if( isset( $this->thisObject ) )
        {                       
            $callback = array( $this->thisObject, $this->functionName );            
        }                       
        else
        {
            $callback = $this->functionName; 
        }
        return( $callback );
    }
    
    private function callCallback( $callback, $arguments )
    {                           
        if( !is_callable( $callback ) )
        {                        
            return( NULL );
        }        
        if( count( $arguments ) == 0 )
        {
            return( call_user_func( $callback ) ); 
        }                
        return( call_user_func_array( $callback, $arguments ) );               
    }
    
    /**
     * Determines if one delegate equals another delegate.
     */
    public function equals( Delegate $delegate )
    {
        return( $this->functionName == $delegate->functionName && $this->thisObject === $delegate->thisObject );
    }        
}

?>