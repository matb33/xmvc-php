<?php

namespace System\Libraries;

/**
 * Represents a dynamic object type like in Javascript.
 * Instances of this type can have any type of property and all properties can be enumerated.
 *
 * @author Darren Schnare
 * @copyright 2010
 */
class Object implements IteratorAggregate
{
    private $properties;

    public function __construct()
    {
        $this->properties = array();
    }

    /**
     * Determines if this object has the specified property set.
     */
    function hasProperty( $propertyName )
    {
        return isset( $this->properties[ $propertyName ] );
    }

    /**
     * Removes the specified property from the object if it exists.
     */
    function removeProperty( $propertyName )
    {
        if( $this->hasProperty( $propertyName ) )
        {
            unset( $this->properties[ $propertyName ] );
        }
    }

    function __set( $propertyName, $value )
    {
        $this->properties[ $propertyName ] = $value;
    }

    function __get( $propertyName )
    {
        $value = $this->properties[ $propertyName ];
        return $value;
    }

    /// IteratorAggregate interface

    public function getIterator()
    {
        return new ArrayIterator( $this->properties );
    }
}