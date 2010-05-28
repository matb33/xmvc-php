<?php

namespace xMVC\Mod\WireKit\FieldConstraints;

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

	public function SetAgainst( $against )
	{
		if( $against !== "" )
		{
			$this->against = $against;
		}
	}

	public function SetMin( $min )
	{
		if( $min !== "" )
		{
			$this->min = $min;
		}
	}

	public function SetMax( $max )
	{
		if( $max !== "" )
		{
			$this->max = $max;
		}
	}

	public function SetDependencyFields( DependencyFields $dependencyFields )
	{
		$this->dependencyFields = $dependencyFields;
	}

	public function SetConstraintMessages( ConstraintMessages $constraintMessages )
	{
		$this->constraintMessages = $constraintMessages;
	}

	public function GetTargetName()
	{
		return $this->targetField->name;
	}

	public function Apply()
	{
		switch( $this->type )
		{
			case "regexp":
				$success = $this->RegExp();
			break;
			case "match":
				$success = $this->Match();
			break;
			case "match-field":
				$success = $this->MatchField();
			break;
			case "match-field-md5":
				$success = $this->MatchFieldMD5();
			break;
			case "selected-count":
				$success = $this->SelectedCount();
			break;
			case "range":
				$success = $this->Range();
			break;
			default:
				return new ConstraintResult( $this, false, "Invalid constraint type." );
		}

		if( $success )
		{
			return new ConstraintResult( $this, true, $this->constraintMessages->GetPassMessage() );
		}
		else
		{
			return new ConstraintResult( $this, false, $this->constraintMessages->GetFailMessage() );
		}
	}

	private function RegExp()
	{
		return preg_match( "/" . $this->against . "/", $this->targetField->value, $matches ) > 0;
	}

	private function Match()
	{
		return $this->targetField->value == $this->against;
	}

	private function MatchField()
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

	private function MatchFieldMD5()
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

	private function SelectedCount()
	{
		$selectedCount = count( $this->targetField->value );

		return $this->WithinRange( $selectedCount, $this->min, $this->max );
	}

	private function Range()
	{
		$floatValue = ( float )$this->targetField->value;

		return $this->WithinRange( $floatValue, $this->min, $this->max );
	}

	private function WithinRange( $value, $min, $max )
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

?>