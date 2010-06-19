<?php

namespace Module\Language\Libraries;

use System\Libraries\Loader;
use System\Drivers\XMLModelDriver;

class Language
{
	private static $languages = null;
	private static $language = null;
	private static $data = null;

	const languageConfig = "xMVC\\App\\languages";
	const languageConfigDefault = "Module\\Language\\Models\\languages.default";

	public static function GetLang()
	{
		self::LoadLanguageModel();
		self::LoadLanguageData();
		self::VerifyDefaultLanguage();

		if( ! self::FindLanguageBasedOnGET() )
		{
			if( ! self::FindLanguageBasedOnSession() )
			{
				if( ! self::FindLanguageBasedOnHost() )
				{
					self::SetLanguageToDefault();
				}
			}
		}

		self::SetLanguageSession();

		return self::$language;
	}

	public static function SetLang( $lang )
	{
		if( in_array( $lang, array_keys( self::$data ) ) )
		{
			self::$language = $lang;
			self::SetLanguageSession();
		}
	}

	public static function GetDefinedLangs()
	{
		self::LoadLanguageModel();
		self::LoadLanguageData();

		$definedLanguages = array_keys( self::$data );

		return $definedLanguages;
	}

	private static function LoadLanguageModel()
	{
		if( is_null( self::$languages ) )
		{
			self::$languages = new XMLModelDriver( self::languageConfig );
		}
	}

	private static function LoadLanguageData()
	{
		if( is_null( self::$data ) )
		{
			foreach( self::$languages->xPath->query( "//lang:languages/lang:language-list/lang:language" ) as $node )
			{
				$id = $node->getAttribute( "id" );

				$hostMatchNodeList = self::$languages->xPath->query( "lang:host-match", $node );
				$hostMatch = $hostMatchNodeList->length > 0 ? $hostMatchNodeList->item( 0 )->nodeValue : "";

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
		return self::$languages->xPath->query( "//lang:languages/lang:config/lang:default" )->item( 0 )->nodeValue;
	}

	private static function FindLanguageBasedOnHost()
	{
		foreach( self::$data as $key => $info )
		{
			if( strlen( $info[ "host-match" ] ) > 0 )
			{
				if( preg_match( $info[ "host-match" ], $_SERVER[ "HTTP_HOST" ] ) )
				{
					self::$language = $key;

					return true;
				}
			}
		}

		return false;
	}

	private static function FindLanguageBasedOnGET()
	{
		if( isset( $_GET[ "lang" ] ) )
		{
			if( in_array( $_GET[ "lang" ], array_keys( self::$data ) ) )
			{
				self::$language = $_GET[ "lang" ];

				return true;
			}
		}

		return false;
	}

	private static function FindLanguageBasedOnSession()
	{
		if( isset( $_SESSION[ "lang" ] ) )
		{
			if( in_array( $_SESSION[ "lang" ], array_keys( self::$data ) ) )
			{
				self::$language = $_SESSION[ "lang" ];

				return true;
			}
		}

		return false;
	}

	private static function SetLanguageToDefault()
	{
		self::$language = self::GetDefaultLanguage();
	}

	private static function SetLanguageSession()
	{
		$_SESSION[ "lang" ] = self::$language;
	}

	public static function GetLangBase( $lang )
	{
		$parts = self::GetLangParts( $lang );

		return $parts[ 0 ];
	}

	public static function GetLangLocale( $lang )
	{
		$parts = self::GetLangParts( $lang );
		$locale = isset( $parts[ 1 ] ) ? $parts[ 1 ] : "";

		return $locale;
	}

	public static function GetLangParts( $lang )
	{
		$parts = explode( "-", $lang, 2 );

		if( !isset( $parts[ 1 ] ) )
		{
			$parts[ 1 ] = "";
		}

		return $parts;
	}

	public static function XSLTLang( $currentLang, $scopeLangData )
	{
		//(ancestor-or-self::*/@xml:lang)[last()]

		if( !is_array( $scopeLangData ) )
		{
			$scopeLang = $scopeLangData;

			if( strlen( $scopeLang ) == 0 )
			{
				$scopeLang = self::GetLang();
			}
		}
		else
		{
			if( isset( $scopeLangData[ 0 ]->value ) )
			{
				$scopeLang = $scopeLangData[ 0 ]->value;
			}
			else
			{
				$scopeLang = self::GetLang();
			}
		}

		if( strlen( $currentLang ) == 0 )
		{
			$currentLang = self::GetLang();
		}

		$scopeLangParts = self::GetLangParts( strtolower( $scopeLang ) );
		$currentLangParts = self::GetLangParts( strtolower( $currentLang ) );

		if( $scopeLangParts[ 0 ] == $currentLangParts[ 0 ] )
		{
			if( strlen( $scopeLangParts[ 1 ] ) == 0 || strlen( $currentLangParts[ 1 ] ) == 0 )
			{
				return true;
			}
			elseif( $scopeLangParts[ 1 ] == $currentLangParts[ 1 ] )
			{
				return true;
			}
		}

		return false;
	}
}