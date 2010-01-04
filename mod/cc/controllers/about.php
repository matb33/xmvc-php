<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FilesystemModelDriver;
use xMVC\Sys\View;

class About extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\standard/about" );

		$view = new View( __NAMESPACE__ . "\\standard" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		$this->PushDependencies( $view );

		$view->RenderAsHTML();
	}
}

?>