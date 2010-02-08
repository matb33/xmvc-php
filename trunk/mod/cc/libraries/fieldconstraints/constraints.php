<?php

namespace Module\CC\FieldConstraints;

use Module\Language\Language;

class Constraints
{
	private $targetField;
	private $sourceModel;

	private static $postFields;

	public function __construct( $name, $value, $sourceModel )
	{
		$this->targetField = new Field( $name, $value );
		$this->sourceModel = $sourceModel;
	}

	public static function Apply( $postFields, $sourceModel )
	{
		self::$postFields = $postFields;

		$constraintResults = array();

		foreach( self::$postFields as $name => $value )
		{
			$constraints = new Constraints( $name, $value, $sourceModel );

			$constraintResults[] = $constraints->GetConstraintResults()->ToArray();
		}

		return( $constraintResults );
	}

	public static function DoTheseResultsValidate( $fields )
	{
		$valid = true;

		foreach( $fields as $field )
		{
			if( is_array( $field[ "results" ] ) )
			{
				foreach( $field[ "results" ] as $result )
				{
					$valid = $valid && $result[ "success" ];
				}
			}
		}

		return( $valid );
	}

	public function GetConstraintResults()
	{
		$constraintResults = new ConstraintResults();

		if( $this->targetField instanceof Field )
		{
			$constraintResults->SetTarget( $this->targetField );

			foreach( $this->sourceModel->xPath->query( "//cc:field[ @name = '" . $this->targetField->name . "' ]/cc:constraint" ) as $constraintNode )
			{
				$type = $constraintNode->getAttribute( "type" );
				$against = $constraintNode->getAttribute( "against" );
				$min = $constraintNode->getAttribute( "min" );
				$max = $constraintNode->getAttribute( "max" );

				$constraint = new Constraint( $type, $this->targetField );

				$constraint->SetAgainst( $against );
				$constraint->SetMin( $min );
				$constraint->SetMax( $max );

				$this->LookForDependencyFields( $constraint, $type, $against );
				$this->LookForMessages( $constraint, $constraintNode );

				$constraintResults->Add( $constraint->Apply() );
			}
		}

		return( $constraintResults );
	}

	private function LookForDependencyFields( &$constraint, $type, $against )
	{
		if( substr( $type, 0, 6 ) == "match-" )
		{
			$dependencyFields = new DependencyFields();
			$value = self::$postFields[ $against ];
			$dependencyFields->Add( new Field( $against, $value ) );
			$constraint->SetDependencyFields( $dependencyFields );
		}
	}

	private function LookForMessages( &$constraint, &$constraintNode )
	{
		$messageNodeList = $this->sourceModel->xPath->query( "cc:message[ @lang = '" . Language::GetLang() . "' ]", $constraintNode );

		$constraintMessages = new ConstraintMessages();

		foreach( $messageNodeList as $messageNode )
		{
			$constraintMessages->Add( $messageNode->getAttribute( "type" ), $messageNode->nodeValue );
		}

		$constraint->SetConstraintMessages( $constraintMessages );
	}
}

?>