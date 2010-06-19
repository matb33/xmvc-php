<?php

namespace xMVC\Sys;

class OutputHeaders
{
	public static function Specifically( $outputType )
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
				self::Custom( $outputType );

		}
	}

	public static function XML()
	{
		header( "Content-type: application/xml; charset=UTF-8" );

		self::NoCache();
	}

	public static function HTML()
	{
		header( "Content-type: text/html; charset=UTF-8" );					// ideally the Content-type would be application/xhtml+xml

		self::NoCache();
	}

	public static function Custom( $header )
	{
		header( $header );
	}

	private static function NoCache()
	{
		header( "Expires: Mon, 14 Oct 2002 05:00:00 GMT" );					// Date in the past
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );	// Always modified
		header( "Cache-Control: no-store, no-cache, must-revalidate" );		// HTTP 1.1
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );										// HTTP 1.0
	}
}

?>