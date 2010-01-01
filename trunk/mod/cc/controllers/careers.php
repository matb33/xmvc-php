<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FilesystemModelDriver;
use xMVC\Sys\View;

class Careers extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\rss/careers" );
		$model = self::ExpandRSSFeeds( $model );

		$view = new View( __NAMESPACE__ . "\\rss" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}
}

?>