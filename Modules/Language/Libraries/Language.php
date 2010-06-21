<?php

namespace Modules\Language\Libraries;

use System\Libraries\Loader;
use System\Drivers\XMLModelDriver;

class Language
{
	private static $languages = null;
	private static $language = null;
	private static $data = null;

	const languageConfig = "Application\\Models\\languages";
	const languageConfigDefault = "Modules\\Language\\Models\\languages.default";

	public static function getLang()
	{
		self::loadLanguageModel();
		self::loadLanguageData();
		self::verifyDefaultLanguage();

		if( ! self::findLanguageBasedOnGET() )
		{
			if( ! self::findLanguageBasedOnSession() )
			{
				if( ! self::findLanguageBasedOnHost() )
				{
					self::setLanguageToDefault();
				}
			}
		}

		self::setLanguageSession();

		return self::$language;
	}

	public static function setLang( $lang )
	{
		if( in_array( $lang, array_keys( self::$data ) ) )
		{
			self::$language = $lang;
			self::setLanguageSession();
		}
	}

	public static function getDefinedLangs()
	{
		self::loadLanguageModel();
		self::loadLanguageData();

		$definedLanguages = array_keys( self::$data );

		return $definedLanguages;
	}

	private static function loadLanguageModel()
	{
		if( is_null( self::$languages ) )
		{
			self::$languages = new XMLModelDriver( self::languageConfig );
		}
	}

	private static function loadLanguageData()
	{
		if( is_null( self::$data ) )
		{
			$nodeList = self::$languages->xPath->query( "//lang:languages/lang:language-list/lang:language" );

			foreach( $nodeList as $node )
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

	private static function verifyDefaultLanguage()
	{
		if( ! in_array( self::getDefaultLanguage(), array_keys( self::$data ) ) )
		{
			self::$language = self::$languages->xPath->query( "//lang:languages/lang:language-list/lang:language[ 1 ]/@lang:id" )->item( 0 )->nodeValue;
		}
	}

	private static function getDefaultLanguage()
	{
		return self::$languages->xPath->query( "//lang:languages/lang:config/lang:default" )->item( 0 )->nodeValue;
	}

	private static function findLanguageBasedOnHost()
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

	private static function findLanguageBasedOnGET()
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

	private static function findLanguageBasedOnSession()
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

	private static function setLanguageToDefault()
	{
		self::$language = self::getDefaultLanguage();
	}

	private static function setLanguageSession()
	{
		$_SESSION[ "lang" ] = self::$language;
	}

	public static function getLangBase( $lang )
	{
		$parts = self::getLangParts( $lang );

		return $parts[ 0 ];
	}

	public static function getLangLocale( $lang )
	{
		$parts = self::getLangParts( $lang );
		$locale = isset( $parts[ 1 ] ) ? $parts[ 1 ] : "";

		return $locale;
	}

	public static function getLangParts( $lang )
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
				$scopeLang = self::getLang();
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
				$scopeLang = self::getLang();
			}
		}

		if( strlen( $currentLang ) == 0 )
		{
			$currentLang = self::getLang();
		}

		$scopeLangParts = self::getLangParts( strtolower( $scopeLang ) );
		$currentLangParts = self::getLangParts( strtolower( $currentLang ) );

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