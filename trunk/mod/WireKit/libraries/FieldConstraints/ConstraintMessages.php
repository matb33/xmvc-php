<?php

namespace xMVC\Mod\WireKit\FieldConstraints;

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
		if( isset( $this->messages[ "fail" ] ) )
		{
			return $this->messages[ "fail" ];
		}

		return "";
	}

	public function GetPassMessage()
	{
		if( isset( $this->messages[ "pass" ] ) )
		{
			return $this->messages[ "pass" ];
		}

		return "";
	}
}

?>