<?php

namespace System\Libraries;

class Routing
{
	private static $URI = null;
	private static $URIProtocol = null;
	private static $pathData = null;
	private static $routeMatches = null;
	private static $routes = array();

	public static function initialize()
	{
		self::URI();
		self::URIProtocol();
		self::gatherRoutesFromConfigs();
	}

	public static function URI()
	{
		if( is_null( self::$URI ) )
		{
			self::$URI = $_SERVER[ "PATH_INFO" ];
			self::$URI = Normalize::URI( self::$URI );
			self::$URI = Normalize::stripQueryInURI( self::$URI );
		}

		return self::$URI;
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

		return self::$URIProtocol;
	}

	private static function gatherRoutesFromConfigs()
	{
		if( isset( Config::$data[ "routes" ] ) )
		{
			self::$routes = Config::$data[ "routes" ];

			if( is_array( self::$routes ) )
			{
				if( isset( Config::$data[ "priorityRoutes" ] ) && is_array( Config::$data[ "priorityRoutes" ] ) )
				{
					self::$routes = array_merge( Config::$data[ "priorityRoutes" ], self::$routes );
				}

				if( isset( Config::$data[ "lowPriorityRoutes" ] ) && is_array( Config::$data[ "lowPriorityRoutes" ] ) )
				{
					self::$routes = array_merge( self::$routes, Config::$data[ "lowPriorityRoutes" ] );
				}
			}
		}
	}

	public static function getPathData()
	{
		return self::$pathData;
	}

	public static function getPathPartsOriginal()
	{
		$pathData = self::getPathData();
		return $pathData[ "pathPartsOriginal" ];
	}

	public static function getPathOnlyOriginal()
	{
		$pathData = self::getPathData();
		return $pathData[ "pathOnlyOriginal" ];
	}

	public static function getPathParts()
	{
		$pathData = self::getPathData();
		return $pathData[ "pathParts" ];
	}

	public static function getPathOnly()
	{
		$pathData = self::getPathData();
		return $pathData[ "pathOnly" ];
	}

	public static function route( $overrideURI = null, $resetRoutesCursor = false )
	{
		if( is_null( $overrideURI ) )
		{
			$URI = self::URI();
		}
		else
		{
			$URI = $overrideURI;
		}

		$routedURI = self::applyRoutingRules( $URI, $resetRoutesCursor );
		self::$pathData = self::getPathDataFromURIs( $URI, $routedURI );
	}

	private static function applyRoutingRules( $URI, $resetRoutesCursor = false )
	{
		if( Config::$data[ "useQueryInRoutes" ] )
		{
			$routedURI = $URI;
		}
		else
		{
			$routedURI = Normalize::stripQueryInURI( $URI );
		}

		if( is_array( self::$routes ) )
		{
			if( $resetRoutesCursor )
			{
				reset( self::$routes );
			}

			while( list( $preg, $replace ) = each( self::$routes ) )
			{
				if( ! is_null( $replace ) )
				{
					if( preg_match( $preg, $routedURI, $routeMatches ) )
					{
						self::$routeMatches = $routeMatches;
						$routedURI = preg_replace_callback( "/%([0-9]+)/", "self::routeReplaceCallback", $replace );
						break;
					}
				}
			}
		}

		return $routedURI;
	}

	private static function routeReplaceCallback( $matches )
	{
		$index = $matches[ 1 ];

		return self::$routeMatches[ $index ];
	}

	private static function getPathDataFromURIs( $URI, $routedURI )
	{
		$pathOnlyOriginal = self::cleanURIForPathData( $URI );
		$pathOnly = self::cleanURIForPathData( $routedURI );

		$pathPartsOriginal = explode( "/", $pathOnlyOriginal );
		$pathParts = explode( "/", $pathOnly );

		$pathData = array();
		$pathData[ "pathOnlyOriginal" ] = $pathOnlyOriginal;
		$pathData[ "pathPartsOriginal" ] = $pathPartsOriginal;
		$pathData[ "pathOnly" ] = $pathOnly;
		$pathData[ "pathParts" ] = $pathParts;

		return $pathData;
	}

	private static function cleanURIForPathData( $uri )
	{
		$uri = Normalize::URI( $uri );
		$uri = Normalize::stripQueryInURI( $uri );

		if( $uri != "/" )
		{
			$uri = ( substr( $uri, 0, 1 ) == "/" ? substr( $uri, 1 ) : $uri );
			$uri = ( substr( $uri, -1 ) == "/" ? substr( $uri, 0, -1 ) : $uri );
		}
		else
		{
			$uri = "";
		}

		return $uri;
	}
}