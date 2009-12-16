<?php

namespace xMVC;

use TinyAuth\Authenticator;
use Language\Language;

class Secret_area
{
	public static function Common()
	{
		Authenticator::Protect();
	}

	public static function Index()
	{
		if( Authenticator::IsAuthenticated() )
		{
			$commonContent = new XMLModelDriver( "content/en/common" );
			$pageContent = new XMLModelDriver( "content/en/secret-area" );

			$data = new StringsModelDriver();
			$data->Add( "lang", Language::GetLang() );
			$data->Add( "logged-in-user", Authenticator::GetUserData( "login" ) );

			$page = new View( "secret-area" );
			$page->PushModel( $commonContent );
			$page->PushModel( $pageContent );
			$page->PushModel( $data );
			$page->RenderAsHTML();
		}
	}

	public static function Logout()
	{
		Authenticator::Logout();

		header( "HTTP/1.1 302 Found\r\n" );
		header( "Location: /\r\n" );
	}
}

?>