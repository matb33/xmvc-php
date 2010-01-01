<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\SQLModelDriver;
use xMVC\Sys\View;

class Contact extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\contact/contact" );

		$view = new View( __NAMESPACE__ . "\\contact" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}

	public function Send()
	{
		$queryData = array();
		$queryData[] = trim( $_POST[ "firstname" ] );
		$queryData[] = trim( $_POST[ "lastname" ] );
		$queryData[] = trim( $_POST[ "email" ] );
		$queryData[] = $_SERVER[ "REMOTE_ADDR" ];

		$entry = new SQLModelDriver( __NAMESPACE__ . "\\contact" );
		$entry->UseQuery( "AddEntry" );
		$entry->SetParameters( $queryData );
		$entry->Execute();

		if( $entry->IsSuccessful() )
		{
			header( "HTTP/1.1 302 Found\r\n" );
			header( "Location: /contact/thanks/\r\n" );
		}
		else
		{
			header( "HTTP/1.1 302 Found\r\n" );
			header( "Location: /contact/error/\r\n" );
		}
	}

	public function Thanks()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\standard/contact-thanks" );

		$view = new View( __NAMESPACE__ . "\\standard" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}

	public function Error()
	{
		$model = new XMLModelDriver( __NAMESPACE__ . "\\standard/contact-error" );

		$view = new View( __NAMESPACE__ . "\\standard" );
		$view->PushModel( $model );
		$view->PushModel( $this->stringData );

		self::PushDependencies( $view, $model );

		$view->RenderAsHTML();
	}
}

?>