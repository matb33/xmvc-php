<?php

namespace Modules\WiredocPHP\Libraries\FieldConstraints;

use ArrayObject;

class DependencyFields extends ArrayObject
{
	public function __construct()
	{
		parent::__construct( array() );
	}

	public function add( Field $field )
	{
		parent::append( $field );
	}
}