<?php

namespace Module\CC;

use xMVC\Sys\Core;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\View;
use Module\CC\FieldConstraints\Constraints;

class ConstraintProcessor
{
	public function __construct()
	{
	}

	public function Index( $sourceViewName, $sourceModelName )
	{
		$sourceModel = new XMLModelDriver( Core::namespaceApp . $sourceViewName . "/" . $sourceModelName );
		$resultsModel = Constraints::Apply( $_POST, $sourceModel );

		$view = new View();
		$view->PushModel( $resultsModel );
		$view->PassThru();
	}
}

?>