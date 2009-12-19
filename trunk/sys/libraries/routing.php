<?php

namespace xMVC\Sys;

class Routing
{
	private static $URI = null;
	private static $URIProtocol = null;
	private static $pathData = null;
	private static $routeMatches = null;

	public static function URI()
	{
		if( is_null( self::$URI ) )
		{
			self::$URI = self::DetermineURI();
		}

		return( self::$URI );
	}

	public static function URIProtocol()
	{
		if( is_null( self::$URIProtocol ) )
		{
			self::$URIProtocol = "http";

			if( isset( $_SERVER[ "HTTPS" ] ) && $_SERVER[ "HTTPS" ] == "on" )
			{
				self::$URIProtocol = "https";
			}

			if( isset( $_SERVER[ "HTTP_SSLSESSIONID" ] ) )
			{
				self::$URIProtocol = "https";
			}
		}

		return( self::$URIProtocol );
	}

	public static function PathData()
	{
		if( is_null( self::$pathData ) )
		{
			self::$pathData = self::DeterminePathData( self::URI() );
		}

		return( self::$pathData );
	}

	private static function DeterminePathData( $URI )
	{
		$routedURI = $URI;

		// Apply routing rules

		$routes = Config::$data[ "routes" ];

		if( isset( $routes ) && is_array( $routes ) )
		{
			if( ! Config::$data[ "useQueryInRoutes" ] )
			{
				$routedURI = preg_replace( "/\?.*$/", "", $routedURI );
			}

			foreach( $routes as $preg => $replace )
			{
				if( preg_match( $preg, $routedURI, $routeMatches ) )
				{
					self::$routeMatches = $routeMatches;

					$routedURI = preg_replace_callback( "/%([0-9]+)/", array( self, "RouteReplaceCallback" ), $replace );

					break;
				}
			}
		}

		$pathOnlyOriginal = substr( $URI, 0, strpos( ( strpos( $URI, "?" ) === false ? ( $URI . "?" ) : $URI ), "?" ) );

		if( $pathOnlyOriginal != "/" )
		{
			$pathOnlyOriginal = ( substr( $pathOnlyOriginal, 0, 1 ) == "/" ? substr( $pathOnlyOriginal, 1 ) : $pathOnlyOriginal );
			$pathOnlyOriginal = ( substr( $pathOnlyOriginal, -1 ) == "/" ? substr( $pathOnlyOriginal, 0, -1 ) : $pathOnlyOriginal );
		}
		else
		{
			$pathOnlyOriginal = "";
		}

		$pathOnly = substr( $routedURI, 0, strpos( ( strpos( $routedURI, "?" ) === false ? ( $routedURI . "?" ) : $routedURI ), "?" ) );

		if( $pathOnly != "/" )
		{
			$pathOnly = ( substr( $pathOnly, 0, 1 ) == "/" ? substr( $pathOnly, 1 ) : $pathOnly );
			$pathOnly = ( substr( $pathOnly, -1 ) == "/" ? substr( $pathOnly, 0, -1 ) : $pathOnly );
		}
		else
		{
			$pathOnly = "";
		}

		$pathPartsOriginal	= explode( "/", $pathOnlyOriginal );
		$pathParts			= explode( "/", $pathOnly );

		$pathData = array();

		$pathData[ "pathOnlyOriginal" ]		= $pathOnlyOriginal;
		$pathData[ "pathPartsOriginal" ]	= $pathPartsOriginal;
		$pathData[ "pathOnly" ]				= $pathOnly;
		$pathData[ "pathParts" ]			= $pathParts;

		return( $pathData );
	}

	private static function RouteReplaceCallback( $matches )
	{
		$index = $matches[ 1 ];

		return( self::$routeMatches[ $index ] );
	}

	private static function DetermineURI()
	{
		if( $_SERVER[ "REQUEST_URI" ] != "" )
		{
			$URI = preg_replace( "/^https?:\/\/" . $_SERVER[ "HTTP_HOST" ] . "/i", "", $_SERVER[ "REQUEST_URI" ] );
		}
		else
		{
			if( $_SERVER[ "PATH_INFO" ] != "" )
			{
				$URI = $_SERVER[ "PATH_INFO" ];
			}
			else
			{
				if( $_SERVER[ "PHP_SELF" ] != "" )
				{
					$URI = $_SERVER[ "PHP_SELF" ];
				}
				else
				{
					if( $_SERVER[ "REDIRECT_URL" ] != "" )
					{
						$URI = $_SERVER[ "REDIRECT_URL" ];
					}
				}

				if( $_SERVER[ "QUERY_STRING" ] != "" )
				{
					$URI .= ( "?" . $_SERVER[ "QUERY_STRING" ] );
				}
			}
		}

		$URI = str_replace( "/index.php", "/", $URI );
		$URI = preg_replace( "/[\/]{2,}/", "/", $URI );

		return( $URI );
	}
}