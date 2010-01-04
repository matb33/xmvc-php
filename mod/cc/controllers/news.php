<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FilesystemModelDriver;
use xMVC\Sys\View;

class News extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\rss/news" );
		$model = $this->ExpandRSSFeeds( $model );

		$view = new View( __NAMESPACE__ . "\\rss" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		$this->PushDependencies( $view );

		$view->RenderAsHTML();
	}
}

?>