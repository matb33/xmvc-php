<?php

namespace Language;

use xMVC\XMLModelDriver;

class Language
{
	private static $languages = null;
	private static $language = null;
	private static $data = null;

	public static function GetLang()
	{
		self::LoadLanguageModel();
		self::LoadLanguageData();
		self::VerifyDefaultLanguage();

		if( ! self::FindLanguageBasedOnHost() )
		{
			if( ! self::FindLanguageBasedOnGET() )
			{
				if( ! self::FindLanguageBasedOnSession() )
				{
					self::SetLanguageToDefault();
				}
			}
		}

		self::SetLanguageSession();

		return( self::$language );
	}

	private static function LoadLanguageModel()
	{
		if( is_null( self::$languages ) )
		{
			self::$languages = new XMLModelDriver( "languages" );
		}
	}

	private static function LoadLanguageData()
	{
		if( is_null( self::$data ) )
		{
			foreach( self::$languages->xPath->query( "//lang:languages/lang:language-list/lang:language" ) as $node )
			{
				$id = $node->getAttribute( "lang:id" );
				$hostMatch = self::$languages->xPath->query( "lang:host-match", $node )->item( 0 )->nodeValue;

				self::$data[ $id ] = array(
					"host-match" => $hostMatch
				);
			}
		}
	}

	private static function VerifyDefaultLanguage()
	{
		if( ! in_array( self::GetDefaultLanguage(), array_keys( self::$data ) ) )
		{
			self::$language = self::$languages->xPath->query( "//lang:languages/lang:language-list/lang:language[ 1 ]/@lang:id" )->item( 0 )->nodeValue;
		}
	}

	private static function GetDefaultLanguage()
	{
		return( self::$languages->xPath->query( "//lang:languages/lang:config/lang:default" )->item( 0 )->nodeValue );
	}

	private static function FindLanguageBasedOnHost()
	{
		foreach( self::$data as $key => $info )
		{
			if( strpos( $_SERVER[ "HTTP_HOST" ], $info[ "host-match" ] ) !== false )
			{
				self::$language = $key;

				return( true );
			}
		}

		return( false );
	}

	private static function FindLanguageBasedOnGET()
	{
		if( isset( $_GET[ "lang" ] ) )
		{
			if( in_array( $_GET[ "lang" ], array_keys( self::$data ) ) )
			{
				self::$language = $_GET[ "lang" ];

				return( true );
			}
		}

		return( false );
	}

	private static function FindLanguageBasedOnSession()
	{
		if( isset( $_SESSION[ "lang" ] ) )
		{
			if( in_array( $_SESSION[ "lang" ], array_keys( self::$data ) ) )
			{
				self::$language = $_SESSION[ "lang" ];

				return( true );
			}
		}

		return( false );
	}

	private static function SetLanguageToDefault()
	{
		self::$language = self::GetDefaultLanguage();
	}

	private static function SetLanguageSession()
	{
		$_SESSION[ "lang" ] = self::$language;
	}
}

?>