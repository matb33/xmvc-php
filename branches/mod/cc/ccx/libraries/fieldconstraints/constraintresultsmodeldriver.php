<?php

namespace xMVC\Mod\CC\FieldConstraints;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\ModelDriverInterface;
use xMVC\Sys\Config;

class ConstraintResultsModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct( array $constraintResultsList )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:constraint-results" );
		$this->appendChild( $this->rootElement );

		$this->TransformForeignToXML( $constraintResultsList );
	}

	public function TransformForeignToXML()
	{
		$constraintResultsList = func_get_arg( 0 );

		$fullSuccess = true;

		foreach( $constraintResultsList as $constraintResults )
		{
			$data = $constraintResults->ToArray();
			$fieldSuccess = true;

			if( isset( $data[ "results" ] ) && is_array( $data[ "results" ] ) )
			{
				$fieldElement = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:field" );
				$nameAttribute = $this->createAttribute( "name" );
				$nameAttribute->value = $data[ "name" ];
				$fieldElement->appendChild( $nameAttribute );
				$this->rootElement->appendChild( $fieldElement );

				foreach( $data[ "results" ] as $resultData )
				{
					$constraintElement = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:constraint-result" );

					$successAttribute = $this->createAttribute( "success" );
					$successAttribute->value = $resultData[ "success" ] ? "true" : "false";
					$constraintElement->appendChild( $successAttribute );

					$typeAttribute = $this->createAttribute( "type" );
					$typeAttribute->value = $resultData[ "constraint" ][ "type" ];
					$constraintElement->appendChild( $typeAttribute );

					$againstAttribute = $this->createAttribute( "against" );
					$againstAttribute->value = $resultData[ "constraint" ][ "against" ];
					$constraintElement->appendChild( $againstAttribute );

					$minAttribute = $this->createAttribute( "min" );
					$minAttribute->value = $resultData[ "constraint" ][ "min" ];
					$constraintElement->appendChild( $minAttribute );

					$maxAttribute = $this->createAttribute( "max" );
					$maxAttribute->value = $resultData[ "constraint" ][ "max" ];
					$constraintElement->appendChild( $maxAttribute );

					$messageData = $this->createCDATASection( ( string )$resultData[ "message" ] );
					$constraintElement->appendChild( $messageData );

					$fieldElement->appendChild( $constraintElement );

					$fieldSuccess = $fieldSuccess && $resultData[ "success" ];
				}

				$fieldSuccessAttribute = $this->createAttribute( "success" );
				$fieldSuccessAttribute->value = $fieldSuccess ? "true" : "false";
				$fieldElement->appendChild( $fieldSuccessAttribute );

				$fullSuccess = $fullSuccess && $fieldSuccess;
			}
		}

		$fullSuccessAttribute = $this->createAttribute( "success" );
		$fullSuccessAttribute->value = $fullSuccess ? "true" : "false";
		$this->rootElement->appendChild( $fullSuccessAttribute );

		parent::TransformForeignToXML();
	}
}

?>