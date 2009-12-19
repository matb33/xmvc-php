<?php

namespace xMVC\App;

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
		$pageContent = new XMLModelDriver( "content/" . $this->lang . "/home" );

		$controllers = new FilesystemModelDriver();
		$controllers->GetFileList( "app/" . Loader::controllerFolder, "/\." . Loader::controllerExtension . "/" );

		$page = new View( "home" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->stringData );
		$page->PushModel( $controllers );
		$page->RenderAsHTML();
	}
}

?>