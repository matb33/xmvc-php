<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FilesystemModelDriver;
use xMVC\Sys\View;

class Root extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Page()
	{
		$path = implode( "/", func_get_args() );

		echo "<pre>";
		var_dump( func_get_args() );
		echo "</pre>";
	}

	public function Sitemap()
	{
		echo( "sitemap.xml contents here" );
	}
}

?>