<?php

namespace System\Libraries;

class OutputHeaders
{
	public static function specifically( $outputType )
	{
		switch( strtolower( $outputType ) )
		{
			case "html":
				self::HTML();
			break;

			case "xml":
				self::XML();
			break;

			default:
				self::custom( $outputType );

		}
	}

	public static function XML()
	{
		header( "Content-type: application/xml; charset=UTF-8" );

		self::noCache();
	}

	public static function HTML()
	{
		header( "Content-type: text/html; charset=UTF-8" );					// ideally the Content-type would be application/xhtml+xml

		self::noCache();
	}

	public static function custom( $header )
	{
		header( $header );
	}

	private static function noCache()
	{
		header( "Expires: Mon, 14 Oct 2002 05:00:00 GMT" );					// Date in the past
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );	// Always modified
		header( "Cache-Control: no-store, no-cache, must-revalidate" );		// HTTP 1.1
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );										// HTTP 1.0
	}
}