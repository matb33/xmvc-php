<?php

namespace Modules\WiredocPHP\Libraries\FieldConstraints;

use System\Libraries\Config;

class Constraint
{
	public $type;
	public $against;
	public $min;
	public $max;

	private $targetField;
	private $dependencyFields;
	private $constraintMessages;

	public function __construct( $type, Field $targetField )
	{
		$this->type = $type;
		$this->targetField = $targetField;
	}

	public function setAgainst( $against )
	{
		if( $against !== "" )
		{
			$this->against = $against;
		}
	}

	public function setMin( $min )
	{
		if( $min !== "" )
		{
			$this->min = $min;
		}
	}

	public function setMax( $max )
	{
		if( $max !== "" )
		{
			$this->max = $max;
		}
	}

	public function setDependencyFields( DependencyFields $dependencyFields )
	{
		$this->dependencyFields = $dependencyFields;
	}

	public function setConstraintMessages( ConstraintMessages $constraintMessages )
	{
		$this->constraintMessages = $constraintMessages;
	}

	public function getTargetName()
	{
		return $this->targetField->name;
	}

	public function apply()
	{
		switch( $this->type )
		{
			case "regexp":
				$success = $this->regExp();
			break;
			case "match":
				$success = $this->match();
			break;
			case "match-field":
				$success = $this->matchField();
			break;
			case "match-field-md5":
				$success = $this->matchFieldMD5();
			break;
			case "selected-count":
				$success = $this->selectedCount();
			break;
			case "range":
				$success = $this->range();
			break;
			case "email":
				$success = $this->email();
			break;
			default:
				return new ConstraintResult( $this, false, "Invalid constraint type." );
		}

		if( $success )
		{
			return new ConstraintResult( $this, true, $this->constraintMessages->getPassMessage() );
		}
		else
		{
			return new ConstraintResult( $this, false, $this->constraintMessages->getFailMessage() );
		}
	}

	private function email()
	{
		return preg_match( "/" . Config::$data[ "validationEmailRegExp" ] . "/", $this->targetField->value, $matches ) > 0;
	}

	private function regExp()
	{
		return preg_match( "/" . $this->against . "/", $this->targetField->value, $matches ) > 0;
	}

	private function match()
	{
		return $this->targetField->value == $this->against;
	}

	private function matchField()
	{
		foreach( $this->dependencyFields->getIterator() as $field )
		{
			if( $field->name == $this->against )
			{
				if( $field->value == $this->targetField->value )
				{
					return true;
				}
			}
		}

		return false;
	}

	private function matchFieldMD5()
	{
		foreach( $this->dependencyFields->getIterator() as $field )
		{
			if( $field->name == $this->against )
			{
				if( $field->value == md5( $this->targetField->value ) )
				{
					return true;
				}
			}
		}

		return false;
	}

	private function selectedCount()
	{
		$selectedCount = count( $this->targetField->value );

		return $this->withinRange( $selectedCount, $this->min, $this->max );
	}

	private function range()
	{
		$floatValue = ( float )$this->targetField->value;

		return $this->withinRange( $floatValue, $this->min, $this->max );
	}

	private function withinRange( $value, $min, $max )
	{
		if( is_null( $min ) && ! is_null( $max ) )
		{
			return $value <= $max;
		}
		else if( ! is_null( $min ) && is_null( $max ) )
		{
			return $value >= $min;
		}
		else
		{
			return $value >= $min && $value <= $max;
		}
	}
}