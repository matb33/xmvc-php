<?php

namespace System\Libraries;

class FrontController
{
	private static $controllerName;
	private static $controllerFile;
	private static $controllerClassName;
	private static $controllerInstance;

	public static function load()
	{
		Routing::initialize();
		self::instantiateController();
	}

	public static function instantiateController()
	{
		self::loadControllerInstance();

		if( self::isIndex() )
		{
			self::invokeIndex();
		}
		else
		{
			self::invokeMethod();
		}
	}

	public static function instantiateNextController()
	{
		self::instantiateController();
	}

	private static function loadControllerInstance()
	{
		self::$controllerName = self::getRoutedController();
		self::$controllerFile = Loader::resolve( Loader::controllerFolder, self::$controllerName, Loader::controllerExtension );

		if( self::$controllerFile === false )
		{
			self::attemptFallbackRoute();
		}

		if( self::$controllerFile !== false )
		{
			self::$controllerClassName = Normalize::className( self::$controllerName );
			self::$controllerInstance = new self::$controllerClassName;
		}
		else
		{
			ErrorHandler::invokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerName, "method" => "N/A" ) );
		}
	}

	private static function attemptFallbackRoute()
	{
		if( isset( Config::$data[ "fallbackRoute" ] ) )
		{
			$originalNamespace = Loader::extractNamespace( Config::$data[ "fallbackRoute" ] );
			$routeAsURI = Loader::stripNamespace( Config::$data[ "fallbackRoute" ] );

			Routing::route( $routeAsURI . Routing::URI(), true );

			self::$controllerName = Loader::assignDefaultNamespace( self::getRoutedController(), $originalNamespace, Loader::controllerFolder );
			self::$controllerFile = Loader::resolve( Loader::controllerFolder, self::$controllerName, Loader::controllerExtension );
		}
	}

	private static function isIndex()
	{
		$pathParts = Routing::getPathParts();

		return count( $pathParts ) <= 1;
	}

	private static function invokeIndex()
	{
		$method = self::getMethod( "Index" );

		self::callMethod( $method );
	}

	private static function invokeMethod()
	{
		$pathParts = Routing::getPathParts();
		$method	= self::getMethod( Normalize::methodName( $pathParts[ 1 ] ) );

		self::callMethod( $method, array_slice( $pathParts, 2 ) );
	}

	private static function getMethod( $methodName )
	{
		$controller = self::$controllerInstance;

		return array( $controller, $methodName );
	}

	private static function callMethod( $method, $parameters = array() )
	{
		if( is_callable( $method ) )
		{
			if( count( $parameters ) )
			{
				call_user_func_array( $method, $parameters );
			}
			else
			{
				call_user_func( $method );
			}
		}
		else
		{
			ErrorHandler::invokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerFile, "method" => $method ) );
		}
	}

	private static function getRoutedController()
	{
		return self::determineController( true );
	}

	private static function getOriginalController()
	{
		return self::determineController( false );
	}

	private static function determineController( $useRoutes = true )
	{
		Routing::route();

		$pathParts = $useRoutes ? Routing::getPathParts() : Routing::getPathPartsOriginal();

		if( $pathParts[ 0 ] != "" )
		{
			$controller = $pathParts[ 0 ];
		}
		else
		{
			$controller = Config::$data[ "defaultController" ];
		}

		$fullyQualifiedController = Loader::assignDefaultNamespace( $controller, null, Loader::controllerFolder );

		return $fullyQualifiedController;
	}
}