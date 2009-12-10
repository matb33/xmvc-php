<?php

class Home
{
	public function Index()
	{
		$commonContent = new XMLModelDriver();
		$commonContent->Load( "content/en/common" );

		$pageContent = new XMLModelDriver();
		$pageContent->Load( "content/en/home" );

		$data = new StringsModelDriver();
		$data->Add( "lang", Language::GetLang() );

		$controllers = new FilesystemModelDriver();
		$controllers->GetFileList( APP_PATH . "controllers", "/\.php/" );

		$page = new View( "home" );
		$page->PushModel( $commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $data );
		$page->PushModel( $controllers );
		$page->RenderAsHTML();
	}
}

?>