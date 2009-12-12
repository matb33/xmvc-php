<?php

namespace xMVC;

class Secret_area
{
	public function __construct()
	{
		TinyAuth::Protect();
	}

	public function Index()
	{
		if( TinyAuth::IsAuthenticated() )
		{
			$commonContent = new XMLModelDriver();
			$commonContent->Load( "content/en/common" );

			$pageContent = new XMLModelDriver();
			$pageContent->Load( "content/en/secret-area" );

			$data = new StringsModelDriver();
			$data->Add( "lang", Language::GetLang() );
			$data->Add( "logged-in-user", TinyAuth::GetUserData( "login" ) );

			$page = new View( "secret-area" );
			$page->PushModel( $commonContent );
			$page->PushModel( $pageContent );
			$page->PushModel( $data );
			$page->RenderAsHTML();
		}
	}

	public function Logout()
	{
		TinyAuth::Logout();

		header( "HTTP/1.1 302 Found\r\n" );
		header( "Location: /\r\n" );
	}
}

?>