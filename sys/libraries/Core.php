<?php

namespace xMVC\Sys;

class Core
{
	const namespaceXML = "http://www.xmvc.org/ns/xmvc/1.0";

	private static $controllerName;
	private static $controllerFile;
	private static $controllerClassName;
	private static $controllerInstance;

	public static function Load()
	{
		if( Config::$data[ "handleErrors" ] )
		{
			ErrorHandler::HandleErrors();
		}

		Routing::Initialize();
		self::InstantiateController();
	}

	public static function InstantiateController()
	{
		self::LoadControllerInstance();

		if( self::IsIndex() )
		{
			self::InvokeIndex();
		}
		else
		{
			self::InvokeMethod();
		}
	}

	public static function InstantiateNextController()
	{
		self::InstantiateController();
	}

	private static function LoadControllerInstance()
	{
		self::$controllerName = self::GetRoutedController();
		self::$controllerFile = Loader::Resolve( Loader::controllerFolder, self::$controllerName, Loader::controllerExtension );

		if( self::$controllerFile === false )
		{
			self::AttemptFallbackRoute();
		}

		if( self::$controllerFile !== false )
		{
			require_once( self::$controllerFile );

			self::$controllerClassName = Normalize::MethodOrClassName( self::$controllerName );
			self::$controllerInstance = new self::$controllerClassName;
		}
		else
		{
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerName, "method" => "N/A" ) );
		}
	}

	private static function AttemptFallbackRoute()
	{
		if( isset( Config::$data[ "fallbackRoute" ] ) )
		{
			$originalNamespace = Loader::ExtractNamespace( Config::$data[ "fallbackRoute" ] );
			$routeAsURI = Loader::StripNamespace( Config::$data[ "fallbackRoute" ] );

			Routing::Route( $routeAsURI . Routing::URI(), true );

			self::$controllerName = Loader::AssignDefaultNamespace( self::GetRoutedController(), $originalNamespace );
			self::$controllerFile = Loader::Resolve( Loader::controllerFolder, self::$controllerName, Loader::controllerExtension );
		}
	}

	private static function IsIndex()
	{
		$pathParts = Routing::GetPathParts();

		return count( $pathParts ) <= 1;
	}

	private static function InvokeIndex()
	{
		$method = self::GetMethod( "Index" );

		self::CallMethod( $method );
	}

	private static function InvokeMethod()
	{
		$pathParts = Routing::GetPathParts();
		$method	= self::GetMethod( Normalize::MethodOrClassName( $pathParts[ 1 ] ) );

		self::CallMethod( $method, array_slice( $pathParts, 2 ) );
	}

	private static function GetMethod( $methodName )
	{
		$controller = self::$controllerInstance;

		return array( $controller, $methodName );
	}

	private static function CallMethod( $method, $parameters = array() )
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
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerFile, "method" => $method ) );
		}
	}

	private static function GetRoutedController()
	{
		return self::DetermineController( true );
	}

	private static function GetOriginalController()
	{
		return self::DetermineController( false );
	}

	private static function DetermineController( $useRoutes = true )
	{
		Routing::Route();

		$pathParts = $useRoutes ? Routing::GetPathParts() : Routing::GetPathPartsOriginal();

		if( $pathParts[ 0 ] != "" )
		{
			$controller = $pathParts[ 0 ];
		}
		else
		{
			$controller = Config::$data[ "defaultController" ];
		}

		$fullyQualifiedController = Loader::AssignDefaultNamespace( $controller );

		return $fullyQualifiedController;
	}

	public static function IsClientSideXSLTSupported()
	{
		if( Config::$data[ "forceServerSideRendering" ] )
		{
			return false;
		}
		else if( Config::$data[ "forceClientSideRendering" ] )
		{
			return true;
		}
		else
		{
			foreach( Config::$data[ "xsltAgents" ] as $preg )
			{
				if( preg_match( $preg, $_SERVER[ "HTTP_USER_AGENT" ] ) )
				{
					return true;
				}
			}
		}

		return false;
	}
}