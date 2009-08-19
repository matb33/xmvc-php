<?php

class Home extends Controller
{
	function Home()
	{
		parent::Controller();
	}

	function Index()
	{
		$data = array( "controllerName" => strtolower( __CLASS__ ) );

		$commonContent = new Model( "xml" );
		$commonContent->xml->Load( "en.common.content" );

		$pageContent = new Model( "xml" );
		$pageContent->xml->Load( "en.home.content" );

		$page = new View();
		$page->PushModel( $commonContent );
		$page->PushModel( $pageContent );
		$page->Render( "home", $data );
	}
}

?>