<?php

namespace xMVC;

class Core
{
	public static $namespace = "http://www.xmvc.org/ns/xmvc/1.0";

	private static $useRoutes;
	private static $controllerName;
	private static $controllerFile;
	private static $controllerInstance;

	public static function Load()
	{
		require( SYS_PATH . "libraries/normalize.php" );
		require( SYS_PATH . "libraries/loader.php" );
		require( SYS_PATH . "libraries/xslt.php" );
		require( SYS_PATH . "libraries/autoload.php" );
		require( SYS_PATH . "autoload.php" );

		require( SYS_PATH . "driver.php" );
		require( SYS_PATH . "view.php" );

		Config::Load( SYS_PATH );
		Config::Load( APP_PATH );

		if( Config::$data[ "handleErrors" ] )
		{
			ErrorHandler::HandleErrors();
		}

		self::InstantiateRootController();
	}

	public static function InstantiateRootController()
	{
		if( self::UnroutedSystemControllerExists() )
		{
			self::LoadUnroutedSystemController();
		}
		else
		{
			self::LoadRoutedController();
		}

		if( self::ControllerExists() )
		{
			self::LoadControllerClass();

			if( self::IsIndex() )
			{
				self::InvokeIndex();
			}
			else
			{
				self::InvokeMethod();
			}
		}
	}

	private static function UnroutedSystemControllerExists()
	{
		if( file_exists( SYS_PATH . "controllers/" . Normalize::Filename( self::GetOriginalController() ) . ".php" ) )
		{
			return( true );
		}

		return( false );
	}

	private static function LoadUnroutedSystemController()
	{
		self::$useRoutes		= false;
		self::$controllerName	= self::GetOriginalController();
		self::$controllerFile	= SYS_PATH . "controllers/" . Normalize::Filename( self::GetOriginalController() ) . ".php";
	}

	private static function LoadRoutedController()
	{
		self::$controllerName		= self::GetRoutedController();

		$applicationControllerFile	= APP_PATH . "controllers/" . Normalize::Filename( self::$controllerName ) . ".php";
		$systemControllerFile		= SYS_PATH . "controllers/" . Normalize::Filename( self::$controllerName ) . ".php";

		if( file_exists( $applicationControllerFile ) )
		{
			self::$controllerFile	= $applicationControllerFile;
			self::$useRoutes		= true;
		}
		else
		{
			self::$controllerFile	= $systemControllerFile;
			self::$useRoutes		= false;
		}
	}

	private static function ControllerExists()
	{
		if( file_exists( self::$controllerFile ) )
		{
			return( true );
		}
		else
		{
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerFile ) );

			return( false );
		}
	}

	private static function LoadControllerClass()
	{
		require_once( self::$controllerFile );

		$controllerClassName = __NAMESPACE__ . "\\" . Normalize::ObjectName( self::$controllerName );

		self::$controllerInstance = new $controllerClassName;
	}

	private static function IsIndex()
	{
		$pathData	= Routing::PathData();
		$pathParts	= self::$useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

		return( count( $pathParts ) <= 1 );
	}

	private static function InvokeIndex()
	{
		self::$controllerInstance->Index();
	}

	private static function InvokeMethod()
	{
		$pathData	= Routing::PathData();
		$pathParts	= self::$useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

		$method		= Normalize::ObjectName( $pathParts[ 1 ] );

		if( method_exists( self::$controllerInstance, $method ) )
		{
			$args = array_slice( $pathParts, 2 );

			array_walk( $args, create_function( '&$value, $key', '$value = ( "\"" . $value . "\"" );' ) );

			eval( "self::\$controllerInstance->\$method( " . implode( ", ", $args ) . " );" );
		}
		else
		{
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerFile, "method" => $method ) );
		}
	}

	private static function GetRoutedController()
	{
		return( self::DetermineController( true ) );
	}

	private static function GetOriginalController()
	{
		return( self::DetermineController( false ) );
	}

	private static function DetermineController( $useRoutes = true )
	{
		$pathData	= Routing::PathData();
		$pathParts	= $useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

		if( $pathParts[ 0 ] != "" )
		{
			$controller = $pathParts[ 0 ];
		}
		else
		{
			$controller = Config::$data[ "defaultController" ];
		}

		return( $controller );
	}

	public static function IsClientSideXSLTSupported()
	{
		$isSupported = false;

		if( Config::$data[ "forceServerSideRendering" ] )
		{
			$isSupported = false;
		}
		else if( Config::$data[ "forceClientSideRendering" ] )
		{
			$isSupported = true;
		}
		else
		{
			foreach( Config::$data[ "xsltAgents" ] as $preg )
			{
				if( preg_match( $preg, $_SERVER[ "HTTP_USER_AGENT" ] ) )
				{
					$isSupported = true;

					break;
				}
			}
		}

		return( $isSupported );
	}
}

?>