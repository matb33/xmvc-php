<?php

class Language
{
	public static function GetLang()
	{
		$language = null;

		$languages = new Model( "xml" );
		$languages->xml->Load( "languages" );

		$defaultLanguage = $languages->xml->xPath->query( "//lang:languages/lang:config/lang:default" )->item( 0 )->nodeValue;

		foreach( $languages->xml->xPath->query( "//lang:languages/lang:language-list/lang:language" ) as $node )
		{
			$id			 = $node->getAttribute( "lang:id" );
			$hostMatch	 = $languages->xml->xPath->query( "lang:host-match", $node )->item( 0 )->nodeValue;

			$data[ $id ] = array(
				"host-match" => $hostMatch
			);
		}

		if( !in_array( $defaultLanguage, array_keys( $data ) ) )
		{
			$language = $languages->xml->xPath->query( "//lang:languages/lang:language-list/lang:language[ 1 ]/@lang:id" )->item( 0 )->nodeValue;
		}

		$language = self::FindLanguageBasedOnHostMatch( $data, $info[ "host-match" ] );

		if( is_null( $language ) )
		{
			$language = self::FindLanguageBasedOnGET( $data );
		}

		if( is_null( $language ) )
		{
			$language = self::FindLanguageBasedOnSession( $data );
		}

		if( is_null( $language ) )
		{
			$language = $defaultLanguage;
		}

		$_SESSION[ "lang" ] = $language;

		return( $language );
	}

	private static function FindLanguageBasedOnHostMatch( $data, $hostMatch )
	{
		foreach( $data as $key => $info )
		{
			if( strpos( $_SERVER[ "HTTP_HOST" ], $hostMatch ) !== false )
			{
				$language = $key;
				break;
			}
		}

		return( $language );
	}

	private static function FindLanguageBasedOnGET( $data )
	{
		if( isset( $_GET[ "lang" ] ) )
		{
			if( in_array( $_GET[ "lang" ], array_keys( $data ) ) )
			{
				$language = $_GET[ "lang" ];
			}
		}

		return( $language );
	}

	private static function FindLanguageBasedOnSession( $data )
	{
		if( isset( $_SESSION[ "lang" ] ) )
		{
			if( in_array( $_SESSION[ "lang" ], array_keys( $data ) ) )
			{
				$language = $_SESSION[ "lang" ];
			}
		}

		return( $language );
	}
}

?>