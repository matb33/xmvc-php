<?php

namespace System\Libraries;

class Normalize
{
	public static function methodName( $name )
	{
		return self::methodOrClassName( $name, true );
	}

	public static function className( $name )
	{
		return self::methodOrClassName( $name, false );
	}

	private static function methodOrClassName( $name, $camelCasing )
	{
		$name = preg_replace( "/-|_/", " ", $name );
		$name = str_replace( "\\", "          ", $name );
		$name = ucwords( $name );

		if( $camelCasing )
		{
			$name = strtolower( substr( $name, 0, 1 ) ) . substr( $name, 1 );
		}

		$name = str_replace( "          ", "\\", $name );
		$name = preg_replace( "/ |\.|%20/", "", $name );

		return $name;
	}

	public static function filename( $name )
	{
		$name = str_replace( "\\", "/", $name );

		return $name;
	}

	public static function path( $path )
	{
		$path = str_replace( "\\", "/", realpath( str_replace( "\\", "/", $path ) ) );
		$path = substr( $path, -1 ) != "/" ? $path . "/" : $path;

		return $path;
	}

	public static function encodeData( $data )
	{
		return "/_enc_" . str_replace( "=", "_", base64_encode( serialize( $data ) ) );
	}

	public static function stripXMLRootTags( $xml )
	{
		$xml = self::stripXMLDeclaration( $xml );
		$xml = self::stripRootTag( $xml );

		return $xml;
	}

	public static function stripXMLDeclaration( $xml )
	{
		return preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xml );
	}

	public static function stripRootTag( $xml )
	{
		$xml = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xml );
		$xml = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xml );

		return $xml;
	}

	public static function stripQueryInURI( $uri )
	{
		return preg_replace( "/\?.*$/", "", $uri );
	}

	public static function URI( $uri )
	{
		$uri = str_replace( "/index.php", "/", $uri );
		$uri = preg_replace( "/[\/]{2,}/", "/", $uri );

		return $uri;
	}
}