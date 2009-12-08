<?php

class Contact_us extends Controller
{
	private $commonContent;
	private $data;

	public function __construct()
	{
		parent::__construct();

		$this->commonContent = new Model( "xml" );
		$this->commonContent->xml->Load( "content/en/common" );

		$this->data = new Model( "strings" );
		$this->data->strings->Add( "lang", Language::GetLang() );
	}

	public function Index()
	{
		$pageContent = new Model( "xml" );
		$pageContent->xml->Load( "content/en/contact-us" );

		$page = new View();
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->data );
		$page->Render( "contact-us" );
	}

	public function Send()
	{
		$queryData = array();
		$queryData[ "firstname" ] = trim( $_POST[ "firstname" ] );
		$queryData[ "lastname" ] = trim( $_POST[ "lastname" ] );
		$queryData[ "email" ] = trim( $_POST[ "email" ] );
		$queryData[ "remote_addr" ] = $_SERVER[ "REMOTE_ADDR" ];

		$entry = new Model( "sql" );
		$entry->sql->Load( "contact-us" );
		$entry->sql->SetQuery( "AddEntry" );
		$entry->sql->SetParameters( $queryData );
		$entry->sql->Execute();

		if( $entry->sql->IsSuccessful() )
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
		$pageContent = new Model( "xml" );
		$pageContent->xml->Load( "content/en/contact-us" );

		$this->data->strings->Add( "type", "thanks" );

		$page = new View();
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->data );
		$page->Render( "contact-us" );
	}

	public function Error()
	{
		$pageContent = new Model( "xml" );
		$pageContent->xml->Load( "content/en/contact-us" );

		$this->data->strings->Add( "type", "error" );

		$page = new View();
		$page->PushModel( $this->commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $this->data );
		$page->Render( "contact-us" );
	}
}

?>