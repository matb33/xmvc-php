<?php

namespace xMVC;

class Core
{
	public static $namespaceXML = "http://www.xmvc.org/ns/xmvc/1.0";
	public static $namespacePHP = __NAMESPACE__;

	public static $controllerExtension = "php";
	public static $modelExtension = "xml";
	public static $viewExtension = "xsl";
	public static $driverExtension = "php";
	public static $libraryExtension = "php";
	public static $configExtension = "php";

	public static $controllerFolder = "controllers";
	public static $modelFolder = "models";
	public static $viewFolder = "views";
	public static $driverFolder = "drivers";
	public static $libraryFolder = "libraries";
	public static $configFolder = "config";

	private static $useRoutes;
	private static $controllerName;
	private static $controllerFile;
	private static $controllerClassName;
	private static $controllerInstance;

	public static function Load()
	{
		require( SYS_PATH . self::$libraryFolder . "/normalize.php" );
		require( SYS_PATH . self::$libraryFolder . "/loader.php" );
		require( SYS_PATH . self::$libraryFolder . "/xslt.php" );
		require( SYS_PATH . self::$libraryFolder . "/autoload.php" );
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
			self::GetUnroutedSystemControllerFilename();
		}
		else
		{
			self::GetRoutedControllerFilename();
		}

		if( self::ControllerExists() )
		{
			self::LoadControllerInstance();

			self::InvokeCommonIfStatic();

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
		if( file_exists( SYS_PATH . self::$controllerFolder . "/" . Normalize::Filename( self::GetOriginalController() ) . "." . self::$controllerExtension ) )
		{
			return( true );
		}

		return( false );
	}

	private static function GetUnroutedSystemControllerFilename()
	{
		self::$useRoutes = false;
		self::$controllerName = self::GetOriginalController();
		self::$controllerFile = SYS_PATH . self::$controllerFolder . "/" . Normalize::Filename( self::GetOriginalController() ) . "." . self::$controllerExtension;
	}

	private static function GetRoutedControllerFilename()
	{
		self::$controllerName = self::GetRoutedController();

		$file = Normalize::Filename( self::$controllerName );
		$path = Loader::FindPathWhereFileExists( self::$controllerFolder, $file, self::$controllerExtension );

		if( $path !== false )
		{
			self::$controllerFile = $path . self::$controllerFolder . "/" . $file . "." . self::$controllerExtension;

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

	private static function LoadControllerInstance()
	{
		require_once( self::$controllerFile );

		self::$controllerClassName = self::AddNamespaceIfMissing( self::$controllerName, self::$namespacePHP );
		self::$controllerClassName = Normalize::ObjectName( self::$controllerClassName );

		if( ! Config::$data[ "useStaticControllers" ] )
		{
			self::$controllerInstance = new self::$controllerClassName;
		}
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

	private static function InvokeCommonIfStatic()
	{
		if( Config::$data[ "useStaticControllers" ] )
		{
			$method = self::GetMethod( "Common" );

			self::CallMethod( $method );
		}
	}

	private static function InvokeIndex()
	{
		$method = self::GetMethod( "Index" );

		self::CallMethod( $method );
	}

	private static function InvokeMethod()
	{
		$pathData = Routing::PathData();
		$pathParts = self::$useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];
		$method	= self::GetMethod( Normalize::ObjectName( $pathParts[ 1 ] ) );

		self::CallMethod( $method, array_slice( $pathParts, 2 ) );
	}

	private static function GetMethod( $methodName )
	{
		if( Config::$data[ "useStaticControllers" ] )
		{
			$controller = self::$controllerClassName;
		}
		else
		{
			$controller = self::$controllerInstance;
		}

		return( array( $controller, $methodName ) );
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
		$pathData = Routing::PathData();
		$pathParts = $useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

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
		if( Config::$data[ "forceServerSideRendering" ] )
		{
			return( false );
		}
		else if( Config::$data[ "forceClientSideRendering" ] )
		{
			return( true );
		}
		else
		{
			foreach( Config::$data[ "xsltAgents" ] as $preg )
			{
				if( preg_match( $preg, $_SERVER[ "HTTP_USER_AGENT" ] ) )
				{
					return( true );
				}
			}
		}

		return( false );
	}
}

?>