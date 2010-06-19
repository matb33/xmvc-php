<?php

namespace xMVC\Sys;

class Routing
{
	private static $URI = null;
	private static $URIProtocol = null;
	private static $pathData = null;
	private static $routeMatches = null;
	private static $routes = array();

	public static function Initialize()
	{
		self::URI();
		self::URIProtocol();
		self::GatherRoutesFromConfigs();
	}

	public static function URI()
	{
		if( is_null( self::$URI ) )
		{
			if( $_SERVER[ "REQUEST_URI" ] != "" )
			{
				self::$URI = preg_replace( "/^https?:\/\/" . $_SERVER[ "HTTP_HOST" ] . "/i", "", $_SERVER[ "REQUEST_URI" ] );
			}
			else
			{
				if( $_SERVER[ "PATH_INFO" ] != "" )
				{
					self::$URI = $_SERVER[ "PATH_INFO" ];
				}
				else
				{
					if( $_SERVER[ "PHP_SELF" ] != "" )
					{
						self::$URI = $_SERVER[ "PHP_SELF" ];
					}
					else
					{
						if( $_SERVER[ "REDIRECT_URL" ] != "" )
						{
							self::$URI = $_SERVER[ "REDIRECT_URL" ];
						}
					}

					if( $_SERVER[ "QUERY_STRING" ] != "" )
					{
						self::$URI .= ( "?" . $_SERVER[ "QUERY_STRING" ] );
					}
				}
			}

			self::$URI = Normalize::URI( self::$URI );
			self::$URI = Normalize::StripQueryInURI( self::$URI );
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

	private static function GatherRoutesFromConfigs()
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

	public static function GetPathData()
	{
		return self::$pathData;
	}

	public static function GetPathPartsOriginal()
	{
		$pathData = self::GetPathData();
		return $pathData[ "pathPartsOriginal" ];
	}

	public static function GetPathOnlyOriginal()
	{
		$pathData = self::GetPathData();
		return $pathData[ "pathOnlyOriginal" ];
	}

	public static function GetPathParts()
	{
		$pathData = self::GetPathData();
		return $pathData[ "pathParts" ];
	}

	public static function GetPathOnly()
	{
		$pathData = self::GetPathData();
		return $pathData[ "pathOnly" ];
	}

	public static function Route( $overrideURI = null, $resetRoutesCursor = false )
	{
		if( is_null( $overrideURI ) )
		{
			$URI = self::URI();
		}
		else
		{
			$URI = $overrideURI;
		}

		$routedURI = self::ApplyRoutingRules( $URI, $resetRoutesCursor );
		self::$pathData = self::GetPathDataFromURIs( $URI, $routedURI );
	}

	private static function ApplyRoutingRules( $URI, $resetRoutesCursor = false )
	{
		if( Config::$data[ "useQueryInRoutes" ] )
		{
			$routedURI = $URI;
		}
		else
		{
			$routedURI = Normalize::StripQueryInURI( $URI );
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
						$routedURI = preg_replace_callback( "/%([0-9]+)/", "self::RouteReplaceCallback", $replace );
						break;
					}
				}
			}
		}

		return $routedURI;
	}

	private static function RouteReplaceCallback( $matches )
	{
		$index = $matches[ 1 ];

		return self::$routeMatches[ $index ];
	}

	private static function GetPathDataFromURIs( $URI, $routedURI )
	{
		$pathOnlyOriginal = self::CleanURIForPathData( $URI );
		$pathOnly = self::CleanURIForPathData( $routedURI );

		$pathPartsOriginal = explode( "/", $pathOnlyOriginal );
		$pathParts = explode( "/", $pathOnly );

		$pathData = array();
		$pathData[ "pathOnlyOriginal" ] = $pathOnlyOriginal;
		$pathData[ "pathPartsOriginal" ] = $pathPartsOriginal;
		$pathData[ "pathOnly" ] = $pathOnly;
		$pathData[ "pathParts" ] = $pathParts;

		return $pathData;
	}

	private static function CleanURIForPathData( $uri )
	{
		$uri = Normalize::URI( $uri );
		$uri = Normalize::StripQueryInURI( $uri );

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