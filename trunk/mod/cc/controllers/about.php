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
		$about = new XMLModelDriver( __NAMESPACE__ . "\\standard/about" );

		$page = new View( __NAMESPACE__ . "\\standard" );
		$page->PushModel( $about );
		$page->PushModel( $this->stringData );

		self::PushDependencies( $page, $about );

		$page->RenderAsHTML();
	}
}

?>