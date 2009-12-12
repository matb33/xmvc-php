<?php

namespace xMVC;

class Contact_us
{
	private $commonContent;
	private $data;

	public function __construct()
	{
		$this->commonContent = new XMLModelDriver();
		$this->commonContent->Load( "content/en/common" );

		$this->data = new StringsModelDriver();
		$this->data->Add( "lang", Language::GetLang() );
	}

	public function Index()
	{
		$pageContent = new XMLModelDriver();
		$pageContent->Load( "content/en/contact-us" );

		$page = new View( "contact-us" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->data );
		$page->RenderAsHTML();
	}

	public function Send()
	{
		$queryData = array();
		$queryData[] = trim( $_POST[ "firstname" ] );
		$queryData[] = trim( $_POST[ "lastname" ] );
		$queryData[] = trim( $_POST[ "email" ] );
		$queryData[] = $_SERVER[ "REMOTE_ADDR" ];

		$entry = new SQLModelDriver();
		$entry->Load( "queries/contact-us" );
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
		$pageContent = new XMLModelDriver();
		$pageContent->Load( "content/en/contact-us" );

		$this->data->Add( "type", "thanks" );

		$page = new View( "contact-us" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->data );
		$page->RenderAsHTML();
	}

	public function Error()
	{
		$pageContent = new XMLModelDriver();
		$pageContent->Load( "content/en/contact-us" );

		$this->data->Add( "type", "error" );

		$page = new View( "contact-us" );
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->data );
		$page->RenderAsHTML();
	}
}

?>