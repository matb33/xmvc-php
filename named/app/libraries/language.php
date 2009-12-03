<?php

class Language
{
	static public function GetLang()
	{
		$language = NULL;

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

		foreach( $data as $key => $info )
		{
			if( strpos( $_SERVER[ "HTTP_HOST" ], $info[ "host-match" ] ) !== false )
			{
				$language = $key;
				break;
			}
		}

		if( is_null( $language ) )
		{
			if( isset( $_GET[ "lang" ] ) )
			{
				if( in_array( $_GET[ "lang" ], array_keys( $data ) ) )
				{
					$language = $_GET[ "lang" ];
				}
			}
		}

		if( is_null( $language ) )
		{
			if( isset( $_SESSION[ "lang" ] ) )
			{
				if( in_array( $_SESSION[ "lang" ], array_keys( $data ) ) )
				{
					$language = $_SESSION[ "lang" ];
				}
			}
		}

		if( is_null( $language ) )
		{
			$language = $defaultLanguage;
		}

		$_SESSION[ "lang" ] = $language;

		return( $language );
	}
}

?>