<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FilesystemModelDriver;
use xMVC\Sys\View;

class Home extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\home/home" );

		$view = new View( __NAMESPACE__ . "\\home" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}
}

?>