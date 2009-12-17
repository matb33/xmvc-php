<?php

namespace xMVC;

use Language\Language;

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
		$queryData = array();
		$queryData[] = trim( $_POST[ "firstname" ] );
		$queryData[] = trim( $_POST[ "lastname" ] );
		$queryData[] = trim( $_POST[ "email" ] );
		$queryData[] = $_SERVER[ "REMOTE_ADDR" ];

		$entry = new SQLModelDriver( "queries/contact-us" );
		$entry->SetQuery( "AddEntry" );
		$entry->SetParameters( $queryData );
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