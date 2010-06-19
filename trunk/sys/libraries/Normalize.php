<?php

namespace xMVC\Sys;

class Normalize
{
	public static function MethodOrClassName( $name )
	{
		$name = preg_replace( "/-|_/", " ", $name );
		$name = str_replace( "\\", "          ", $name );
		$name = ucwords( $name );
		$name = str_replace( "          ", "\\", $name );
		$name = preg_replace( "/ |\.|%20/", "", $name );
		$name = str_replace( "XMVC", "xMVC", $name );

		return $name;
	}

	public static function Filename( $name )
	{
		$name = str_replace( "\\", "/", $name );

		return $name;
	}

	public static function Path( $path )
	{
		$path = str_replace( "\\", "/", realpath( str_replace( "\\", "/", $path ) ) );
		$path = substr( $path, -1 ) != "/" ? $path . "/" : $path;

		return $path;
	}

	public static function EncodeData( $data )
	{
		return "/_enc_" . str_replace( "=", "_", base64_encode( serialize( $data ) ) );
	}

	public static function StripXMLRootTags( $xml )
	{
		$xml = self::StripXMLDeclaration( $xml );
		$xml = self::StripRootTag( $xml );

		return $xml;
	}

	public static function StripXMLDeclaration( $xml )
	{
		return preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xml );
	}

	public static function StripRootTag( $xml )
	{
		$xml = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xml );
		$xml = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xml );

		return $xml;
	}

	public static function StripQueryInURI( $uri )
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