<?php

class xMVC
{
	public static $namespace = "http://www.xmvc.org/ns/xmvc/1.0";

	public static function Load()
	{
		require( SYS_PATH . "libraries/normalize.php" );
		require( SYS_PATH . "libraries/loader.php" );
		require( SYS_PATH . "libraries/xslt.php" );
		require( SYS_PATH . "libraries.php" );

		require( SYS_PATH . "controller.php" );
		require( SYS_PATH . "model.php" );
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
		$controllerNameOriginal = self::GetOriginalController();
		$controllerNameRouted	= self::GetRoutedController();

		// Check to see if the original non-routed controller is available as a sys controller.  If it exists, it should load above all else.

		$systemControllerFile = SYS_PATH . "controllers/" . Normalize::Filename( $controllerNameOriginal ) . ".php";

		if( file_exists( $systemControllerFile ) )
		{
			// Priority sys controller

			$useRoutes		= false;
			$controllerName	= $controllerNameOriginal;
			$controllerFile = $systemControllerFile;
		}
		else
		{
			// Routed controller, normal operation

			$controllerName	= $controllerNameRouted;

			$applicationControllerFile	= APP_PATH . "controllers/" . Normalize::Filename( $controllerName ) . ".php";
			$systemControllerFile		= SYS_PATH . "controllers/" . Normalize::Filename( $controllerName ) . ".php";

			if( file_exists( $applicationControllerFile ) )
			{
				$controllerFile = $applicationControllerFile;
				$useRoutes		= true;
			}
			else
			{
				$controllerFile = $systemControllerFile;
				$useRoutes		= false;
			}
		}

		if( file_exists( $controllerFile ) )
		{
			require_once( $controllerFile );

			$controllerClassName = Normalize::ObjectName( $controllerName );

			$controllerInstance = new $controllerClassName;

			$pathData	= Routing::PathData();
			$pathParts	= $useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

			if( count( $pathParts ) <= 1 )
			{
				$controllerInstance->Index();
			}
			else
			{
				$method = Normalize::ObjectName( $pathParts[ 1 ] );

				if( method_exists( $controllerInstance, $method ) )
				{
					$args = array_slice( $pathParts, 2 );

					array_walk( $args, create_function( '&$value, $key', '$value = ( "\"" . $value . "\"" );' ) );

					eval( "\$controllerInstance->\$method( " . implode( ", ", $args ) . " );" );
				}
				else
				{
					ErrorHandler::InvokeError( "http-error", "http-errors", array( "errorCode" => "404", "controllerFile" => $controllerFile, "method" => $method ) );
				}
			}
		}
		else
		{
			ErrorHandler::InvokeError( "http-error", "http-errors", array( "errorCode" => "404", "controllerFile" => $controllerFile ) );
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