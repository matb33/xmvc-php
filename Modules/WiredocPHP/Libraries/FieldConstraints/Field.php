<?php

namespace Modules\WiredocPHP\Libraries\FieldConstraints;

class Field
{
	public $name;
	public $value;
	public $originalName;

	public function __construct( $name, $value )
	{
		$this->name = preg_replace( "[\[\]]", "", $name );
		$this->originalName = $name;

		if( $value === "NULL" )
		{
			$value = null;
		}

		$this->value = $value;

	}
}