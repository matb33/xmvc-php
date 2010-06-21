<?php

namespace Modules\WiredocPHP\Libraries\FieldConstraints;

class ConstraintMessages
{
	private $messages;

	public function __construct()
	{
		$messages = array();
	}

	public function add( $type, $message )
	{
		$this->messages[ $type ] = $message;
	}

	public function getFailMessage()
	{
		if( isset( $this->messages[ "fail" ] ) )
		{
			return $this->messages[ "fail" ];
		}

		return "";
	}

	public function getPassMessage()
	{
		if( isset( $this->messages[ "pass" ] ) )
		{
			return $this->messages[ "pass" ];
		}

		return "";
	}
}