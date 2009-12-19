<?php

namespace xMVC\Sys;

class Core
{
	const namespaceXML = "http://www.xmvc.org/ns/xmvc/1.0";
	const namespaceApp = "xMVC\\App\\";
	const namespaceSys = "xMVC\\Sys\\";

	//private static $useRoutes;
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

		self::InstantiateRootController();
	}

	public static function InstantiateRootController()
	{
		//if( self::UnroutedSystemControllerExists() )
		//{
		//	self::GetUnroutedSystemControllerFilename();
		//}
		//else
		//{
		//	self::GetRoutedControllerFilename();
		//}

		self::GetRoutedControllerFilename();

		if( self::ControllerExists() )
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
	}

	/*
	private static function UnroutedSystemControllerExists()
	{
		if( file_exists( SYS_PATH . Loader::controllerFolder . "/" . Normalize::Filename( self::GetOriginalController() ) . "." . Loader::controllerExtension ) )
		{
			return( true );
		}

		return( false );
	}

	private static function GetUnroutedSystemControllerFilename()
	{
		self::$useRoutes = false;
		self::$controllerName = self::GetOriginalController();
		self::$controllerFile = SYS_PATH . Loader::controllerFolder . "/" . Normalize::Filename( self::GetOriginalController() ) . "." . Loader::controllerExtension;
	}
	*/

	private static function GetRoutedControllerFilename()
	{
		self::$controllerName = self::GetRoutedController();

		$file = self::$controllerName;
		$path = Loader::FindPathWhereFileExists( Loader::controllerFolder, $file, Loader::controllerExtension );

		if( $path !== false )
		{
			self::$controllerFile = $path . $file;

			/*
			if( $path == SYS_PATH )
			{
				self::$useRoutes = false;
			}
			else
			{
				self::$useRoutes = true;
			}
			*/
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

		self::$controllerClassName = Normalize::ObjectName( self::$controllerName );
		self::$controllerInstance = new self::$controllerClassName;
	}

	private static function IsIndex()
	{
		$pathData	= Routing::PathData();
		$pathParts	= $pathData[ "pathParts" ];	//self::$useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

		return( count( $pathParts ) <= 1 );
	}

	private static function InvokeIndex()
	{
		$method = self::GetMethod( "Index" );

		self::CallMethod( $method );
	}

	private static function InvokeMethod()
	{
		$pathData = Routing::PathData();
		$pathParts = $pathData[ "pathParts" ];	//self::$useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];
		$method	= self::GetMethod( Normalize::ObjectName( $pathParts[ 1 ] ) );

		self::CallMethod( $method, array_slice( $pathParts, 2 ) );
	}

	private static function GetMethod( $methodName )
	{
		$controller = self::$controllerInstance;

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

		$fullyQualifiedController = Loader::AssignDefaultNamespace( $controller );

		return( $fullyQualifiedController );
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