<?php

namespace xMVC;

class Core
{
	public static $namespace = "http://www.xmvc.org/ns/xmvc/1.0";

	private static $useRoutes;
	private static $controllerName;
	private static $controllerFile;
	private static $controllerClassName;

	public static function Load()
	{
		require( SYS_PATH . "libraries/normalize.php" );
		require( SYS_PATH . "libraries/loader.php" );
		require( SYS_PATH . "libraries/xslt.php" );
		require( SYS_PATH . "libraries/autoload.php" );
		require( SYS_PATH . "autoload.php" );

		require( SYS_PATH . "driver.php" );
		require( SYS_PATH . "view.php" );

		Config::Load();

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

			self::InvokeCommon();

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
		self::$useRoutes = false;
		self::$controllerName = self::GetOriginalController();
		self::$controllerFile = SYS_PATH . "controllers/" . Normalize::Filename( self::GetOriginalController() ) . ".php";
	}

	private static function LoadRoutedController()
	{
		self::$controllerName = self::GetRoutedController();

		$file = Normalize::Filename( self::$controllerName );
		$path = Loader::FindPathWhereFileExists( "controllers", $file, "php" );

		if( $path !== false )
		{
			self::$controllerFile = $path . "controllers/" . $file . ".php";

			if( $path == SYS_PATH )
			{
				self::$useRoutes = false;
			}
			else
			{
				self::$useRoutes = true;
			}
		}
		else
		{
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => $file, "method" => "N/A" ) );
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
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerFile, "method" => "N/A" ) );

			return( false );
		}
	}

	private static function LoadControllerClass()
	{
		require_once( self::$controllerFile );

		self::$controllerClassName = self::AddNamespaceIfMissing( self::$controllerName, __NAMESPACE__ );
		self::$controllerClassName = Normalize::ObjectName( self::$controllerClassName );
	}

	private static function AddNamespaceIfMissing( $name, $namespace )
	{
		if( strpos( $name, "\\" ) === false )
		{
			return( $namespace . "\\" . $name );
		}
		else
		{
			return( $name );
		}
	}

	private static function IsIndex()
	{
		$pathData	= Routing::PathData();
		$pathParts	= self::$useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

		return( count( $pathParts ) <= 1 );
	}

	private static function InvokeCommon()
	{
		$commonMethod = array( self::$controllerClassName, "Common" );

		if( is_callable( $commonMethod ) )
		{
			call_user_func( $commonMethod );
		}
	}

	private static function InvokeIndex()
	{
		$indexMethod = array( self::$controllerClassName, "Index" );

		if( is_callable( $indexMethod ) )
		{
			call_user_func( $indexMethod );
		}
		else
		{
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerFile, "method" => implode( "::", $indexMethod ) ) );
		}
	}

	private static function InvokeMethod()
	{
		$pathData = Routing::PathData();
		$pathParts = self::$useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

		$method	= array( self::$controllerClassName, Normalize::ObjectName( $pathParts[ 1 ] ) );

		if( is_callable( $method ) )
		{
			call_user_func_array( $method, array_slice( $pathParts, 2 ) );
		}
		else
		{
			ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => self::$controllerFile, "method" => implode( "::", $method ) ) );
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