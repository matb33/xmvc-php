<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FilesystemModelDriver;
use xMVC\Sys\View;

class Legal extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\standard/legal" );

		$view = new View( __NAMESPACE__ . "\\standard" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}

	public function Terms_of_use()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\standard/terms-of-use" );

		$view = new View( __NAMESPACE__ . "\\standard" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}

	public function Privacy_policy()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\standard/privacy-policy" );

		$view = new View( __NAMESPACE__ . "\\standard" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}
}

?>