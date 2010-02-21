<?php

namespace xMVC\App;

use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\SQLModelDriver;
use xMVC\Sys\View;

class Contact_us extends Website
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		$pageContent = new XMLModelDriver( "content/en/contact-us" );

		$page = new View( "contact-us" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->stringData );
		$page->RenderAsHTML();
	}

	public function Send()
	{
		$entry = new SQLModelDriver( "queries/contact-us" );
		$entry->UseQuery( "AddEntry" );
		$entry->AddParameter( trim( $_POST[ "firstname" ] ) );
		$entry->AddParameter( trim( $_POST[ "lastname" ] ) );
		$entry->AddParameter( trim( $_POST[ "email" ] ) );
		$entry->AddParameter( $_SERVER[ "REMOTE_ADDR" ] );
		$entry->Execute();

		if( $entry->IsSuccessful() )
		{
			header( "HTTP/1.1 302 Found\r\n" );
			header( "Location: /contact-us/thanks/\r\n" );
		}
		else
		{
			header( "HTTP/1.1 302 Found\r\n" );
			header( "Location: /contact-us/error/\r\n" );
		}
	}

	public function Thanks()
	{
		$pageContent = new XMLModelDriver( "content/en/contact-us" );

		$this->stringData->Add( "type", "thanks" );

		$page = new View( "contact-us" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->stringData );
		$page->RenderAsHTML();
	}

	public function Error()
	{
		$pageContent = new XMLModelDriver( "content/en/contact-us" );

		$this->stringData->Add( "type", "error" );

		$page = new View( "contact-us" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->stringData );
		$page->RenderAsHTML();
	}
}

?>