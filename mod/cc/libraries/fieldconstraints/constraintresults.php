<?php

namespace Module\CC\FieldConstraints;

use ArrayObject;

class ConstraintResults extends ArrayObject
{
	private $target;

	public function __construct()
	{
		parent::__construct( array() );
	}

	public function SetTarget( Field $target )
	{
		$this->target = $target;
	}

	public function Add( ConstraintResult $result )
	{
		parent::append( $result );
	}

	public function ToArray()
	{
		$resultSet = array();

		$resultSet[ "name" ] = $this->target->originalName;

		foreach( parent::getIterator() as $result )
		{
			$results = array();
			$results[ "constraint" ] = array( "type" => $result->constraint->type, "against" => $result->constraint->against, "min" => $result->constraint->min, "max" => $result->constraint->max );
			$results[ "success" ] = $result->success;
			$results[ "message" ] = $result->message;

			$resultSet[ "results" ][] = $results;
		}

		return( $resultSet );
	}
}

?>