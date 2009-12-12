<?php

namespace xMVC;

class Contact_us
{
	private static $commonContent;
	private static $data;

	public static function Common()
	{
		self::$commonContent = new XMLModelDriver();
		self::$commonContent->Load( "content/en/common" );

		self::$data = new StringsModelDriver();
		self::$data->Add( "lang", Language::GetLang() );
	}

	public static function Index()
	{
		$pageContent = new XMLModelDriver();
		$pageContent->Load( "content/en/contact-us" );

		$page = new View( "contact-us" );
		$page->PushModel( self::$commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( self::$data );
		$page->RenderAsHTML();
	}

	public static function Send()
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

	public static function Thanks()
	{
		$pageContent = new XMLModelDriver();
		$pageContent->Load( "content/en/contact-us" );

		self::$data->Add( "type", "thanks" );

		$page = new View( "contact-us" );
		$page->PushModel( self::$commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( self::$data );
		$page->RenderAsHTML();
	}

	public static function Error()
	{
		$pageContent = new XMLModelDriver();
		$pageContent->Load( "content/en/contact-us" );

		self::$data->Add( "type", "error" );

		$page = new View( "contact-us" );
		$page->PushModel( self::$commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( self::$data );
		$page->RenderAsHTML();
	}
}

?>