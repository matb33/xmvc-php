<?php

namespace Modules\WiredocPHP\Libraries\FieldConstraints;

use Modules\Language\Libraries\Language;

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

	public static function apply( $postFields, $sourceModel )
	{
		self::$postFields = $postFields;

		$constraintResultsList = array();

		foreach( self::$postFields as $name => $value )
		{
			$constraints = new Constraints( $name, $value, $sourceModel );

			$constraintResultsList[] = $constraints->getConstraintResults();
		}

		$resultsModel = new ConstraintResultsModelDriver( $constraintResultsList );

		return $resultsModel;
	}

	public function getConstraintResults()
	{
		$constraintResults = new ConstraintResults();

		if( $this->targetField instanceof Field )
		{
			$constraintResults->setTarget( $this->targetField );
			$constraintNodeList = $this->sourceModel->xPath->query( "//wd:field[ @name = '" . $this->targetField->name . "' ]/wd:constraint" );

			foreach( $constraintNodeList as $constraintNode )
			{
				$type = $constraintNode->getAttribute( "type" );
				$against = $constraintNode->getAttribute( "against" );
				$min = $constraintNode->getAttribute( "min" );
				$max = $constraintNode->getAttribute( "max" );

				$constraint = new Constraint( $type, $this->targetField );

				$constraint->setAgainst( $against );
				$constraint->setMin( $min );
				$constraint->setMax( $max );

				$this->lookForDependencyFields( $constraint, $type, $against );
				$this->lookForMessages( $constraint, $constraintNode );

				$constraintResults->add( $constraint->apply() );
			}
		}

		return $constraintResults;
	}

	private function lookForDependencyFields( &$constraint, $type, $against )
	{
		if( substr( $type, 0, 6 ) == "match-" )
		{
			$dependencyFields = new DependencyFields();
			$value = self::$postFields[ $against ];
			$dependencyFields->add( new Field( $against, $value ) );
			$constraint->setDependencyFields( $dependencyFields );
		}
	}

	private function lookForMessages( &$constraint, &$constraintNode )
	{
		$messageNodeList = $this->sourceModel->xPath->query( "wd:message[ @lang = '" . Language::getLang() . "' ]", $constraintNode );

		$constraintMessages = new ConstraintMessages();

		foreach( $messageNodeList as $messageNode )
		{
			$constraintMessages->add( $messageNode->getAttribute( "type" ), $messageNode->nodeValue );
		}

		$constraint->setConstraintMessages( $constraintMessages );
	}
}