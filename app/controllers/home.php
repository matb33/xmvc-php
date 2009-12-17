<?php

namespace xMVC;

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
		$controllers->GetFileList( APP_PATH . Core::$controllerFolder, "/\." . Core::$controllerExtension . "/" );

		$page = new View( "home" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->stringData );
		$page->PushModel( $controllers );
		$page->RenderAsHTML();
	}
}

?>