<?php

namespace xMVC;

use TinyAuth\Authenticator;

class Secret_area extends Website
{
	public function __construct()
	{
		parent::__construct();

		Authenticator::Protect();
	}

	public function Index()
	{
		if( Authenticator::IsAuthenticated() )
		{
			$pageContent = new XMLModelDriver( "content/" . $this->lang . "/secret-area" );

			$this->stringData->Add( "logged-in-user", Authenticator::GetUserData( "login" ) );

			$page = new View( "secret-area" );
			$page->PushModel( $this->commonContent );
			$page->PushModel( $pageContent );
			$page->PushModel( $this->stringData );
			$page->RenderAsHTML();
		}
	}

	public function Logout()
	{
		Authenticator::Logout();

		header( "HTTP/1.1 302 Found\r\n" );
		header( "Location: /\r\n" );
	}
}

?>