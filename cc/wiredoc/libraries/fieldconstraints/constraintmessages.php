<?php

namespace Module\CC\FieldConstraints;

class ConstraintMessages
{
	private $messages;

	public function __construct()
	{
		$messages = array();
	}

	public function Add( $type, $message )
	{
		$this->messages[ $type ] = $message;
	}

	public function GetFailMessage()
	{
		return( $this->messages[ "fail" ] );
	}

	public function GetPassMessage()
	{
		return( $this->messages[ "pass" ] );
	}
}

?>