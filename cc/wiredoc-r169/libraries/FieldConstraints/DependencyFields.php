<?php

namespace xMVC\Mod\CC\FieldConstraints;

use ArrayObject;

class DependencyFields extends ArrayObject
{
	public function __construct()
	{
		parent::__construct( array() );
	}

	public function Add( Field $field )
	{
		parent::append( $field );
	}
}

?>