<?php

namespace System\Libraries;

class OutputHeaders
{
	public static function specifically( $outputType, $cacheTime = 0 )
	{
		switch( strtolower( $outputType ) )
		{
			case "html":
				self::HTML( $cacheTime );
			break;

			case "xml":
				self::XML( $cacheTime );
			break;

			default:
				self::custom( $outputType );

		}
	}

	public static function XML( $cacheTime = 0 )
	{
		header( "Content-type: application/xml; charset=UTF-8" );

		self::cacheHeaders( $cacheTime );
	}

	public static function HTML( $cacheTime = 0 )
	{
		header( "Content-type: text/html; charset=UTF-8" );					// ideally the Content-type would be application/xhtml+xml

		self::cacheHeaders( $cacheTime );
	}

	public static function custom( $header )
	{
		header( $header );
	}

	private static function cacheHeaders( $cacheTime )
	{
		if( $cacheTime <= 0 )
		{
			self::noCache();
		}
		else
		{
			self::maxAgeCache( $cacheTime );
		}
	}

	public static function maxAgeCache( $cacheTime )
	{
		header( "Cache-Control: max-age=" . $cacheTime . ", must-revalidate" );
		header( "Pragma: cache" );
	}

	public static function noCache()
	{
		header( "Expires: Mon, 14 Oct 2002 05:00:00 GMT" );					// Date in the past
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );	// Always modified
		header( "Cache-Control: no-store, no-cache, must-revalidate" );		// HTTP 1.1
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );										// HTTP 1.0
	}
}