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

	public static function PathData( $overrideURI = null )
	{
		$URI = self::URI();

		if( !is_null( $overrideURI ) )
		{
			$URI = $overrideURI;

			self::$pathData = null;
		}

		if( is_null( self::$pathData ) )
		{
			self::$pathData = self::GetPathData( $URI );
		}

		return( self::$pathData );
	}

	private static function GetPathData( $URI )
	{
		$routedURI = self::ApplyRoutingRules( $URI );

		return( self::GetPathDataFromURIs( $URI, $routedURI ) );
	}

	private static function ApplyRoutingRules( $URI )
	{
		$routedURI = $URI;

		$routes = Config::$data[ "routes" ];

		if( isset( $routes ) && is_array( $routes ) )
		{
			if( isset( Config::$data[ "priorityRoutes" ] ) && is_array( Config::$data[ "priorityRoutes" ] ) )
			{
				$routes = array_merge( Config::$data[ "priorityRoutes" ], $routes );
			}

			if( ! Config::$data[ "useQueryInRoutes" ] )
			{
				$routedURI = preg_replace( "/\?.*$/", "", $routedURI );
			}

			foreach( $routes as $preg => $replace )
			{
				if( ! is_null( $replace ) )
				{
					if( preg_match( $preg, $routedURI, $routeMatches ) )
					{
						self::$routeMatches = $routeMatches;
						$routedURI = preg_replace_callback( "/%([0-9]+)/", array( self, "RouteReplaceCallback" ), $replace );
						break;
					}
				}
			}
		}

		return( $routedURI );
	}

	private static function RouteReplaceCallback( $matches )
	{
		$index = $matches[ 1 ];

		return( self::$routeMatches[ $index ] );
	}

	private static function GetPathDataFromURIs( $URI, $routedURI )
	{
		$pathOnlyOriginal = self::CleanURI( $URI );
		$pathOnly = self::CleanURI( $routedURI );

		$pathPartsOriginal = explode( "/", $pathOnlyOriginal );
		$pathParts = explode( "/", $pathOnly );

		$pathData = array();
		$pathData[ "pathOnlyOriginal" ] = $pathOnlyOriginal;
		$pathData[ "pathPartsOriginal" ] = $pathPartsOriginal;
		$pathData[ "pathOnly" ] = $pathOnly;
		$pathData[ "pathParts" ] = $pathParts;

		return( $pathData );
	}

	private static function CleanURI( $path )
	{
		$path = substr( $path, 0, strpos( ( strpos( $path, "?" ) === false ? ( $path . "?" ) : $path ), "?" ) );

		if( $path != "/" )
		{
			$path = ( substr( $path, 0, 1 ) == "/" ? substr( $path, 1 ) : $path );
			$path = ( substr( $path, -1 ) == "/" ? substr( $path, 0, -1 ) : $path );
		}
		else
		{
			$path = "";
		}

		return( $path );
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