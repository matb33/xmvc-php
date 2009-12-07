<?php

class Home extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$commonContent = new Model( "xml" );
		$commonContent->xml->Load( "content/en/common" );

		$pageContent = new Model( "xml" );
		$pageContent->xml->Load( "content/en/home" );

		$data = new Model( "strings" );
		$data->strings->Add( "lang", Language::GetLang() );

		$page = new View();
		$page->PushModel( $commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $data );
		$page->Render( "home" );
	}
}

?>