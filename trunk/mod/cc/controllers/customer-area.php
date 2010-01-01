<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\View;

use Module\TinyAuth\Authenticator;

class Customer_area extends Website
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
			$model = new XMLModelDriver( __NAMESPACE__ . "\\standard/customer-area" );

			$this->stringData->Add( "logged-in-user", Authenticator::GetUserData( "login" ) );

			$model = self::ExpandGetStrings( $model, $this->stringData );

			$view = new View( __NAMESPACE__ . "\\standard" );
			$view->PushModel( $model );
			$view->PushModel( $this->stringData );

			self::PushDependencies( $view, $model );

			$view->RenderAsHTML();
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