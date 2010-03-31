<?php

namespace xMVC\Mod\CC\FieldConstraints;

class ConstraintResult
{
	public $success = false;
	public $message = "";
	public $constraint;

	public function __construct( Constraint $constraint, $success, $message )
	{
		$this->constraint = $constraint;
		$this->success = $success;
		$this->message = $message;
	}
}

?>