<?php

class xMVC
{
	// Public methods

	function URI()
	{
		static $storedValue;

		$storedValue = isset( $storedValue ) ? $storedValue : xMVC::__DetermineURI();

		return( $storedValue );
	}

	function URIProtocol()
	{
		static $storedValue;

		$storedValue = isset( $storedValue ) ? $storedValue : ( ( isset( $_SERVER[ "HTTPS" ] ) && $_SERVER[ "HTTPS" ] == "on" ) || isset( $_SERVER[ "HTTP_SSLSESSIONID" ] ) ? "https" : "http" );

		return( $storedValue );
	}

	function PathData()
	{
		static $storedValue;

		$storedValue = isset( $storedValue ) ? $storedValue : xMVC::__DeterminePathData( xMVC::URI() );

		return( $storedValue );
	}

	function Controller()
	{
		static $controllerRouted;
		static $controllerOriginal;

		$controllerRouted	= isset( $controllerRouted )	? $controllerRouted		: xMVC::__DetermineController( true );
		$controllerOriginal = isset( $controllerOriginal )	? $controllerOriginal	: xMVC::__DetermineController( false );

		return( array( $controllerRouted, $controllerOriginal ) );
	}

	function DefaultController( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = $value;
		}

		return( $storedValue );
	}

	function RootController( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = $value;
		}

		return( $storedValue );
	}

	function Routes( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = $value;
		}

		return( $storedValue );
	}

	function SourceViewEnabled( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = ( bool )$value;
		}

		return( $storedValue );
	}

	function SourceViewKey( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = $value;
		}

		return( $storedValue );
	}

	function HandleErrors( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = ( bool )$value;
		}

		return( $storedValue );
	}

	function ErrorHandler( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = $value;
		}

		return( $storedValue );
	}

	function Database( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = array(
				"databaseHost" => $value[ 0 ],
				"databaseName" => $value[ 1 ],
				"databaseUser" => $value[ 2 ],
				"databasePass" => $value[ 3 ],
				"databaseType" => $value[ 4 ],
			);
		}

		return( $storedValue );
	}

	function IsClientSideXSLTSupported()
	{
		$isSupported = false;

		if( Config::Value( "forceServerSideRendering" ) === "true" )
		{
			$isSupported = false;
		}
		else if( Config::Value( "forceClientSideRendering" ) === "true" )
		{
			$isSupported = true;
		}
		else
		{
			$supportedAgents = Config::Value( "xsltAgents" );

			foreach( $supportedAgents as $preg )
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

	function StripRootTags( $xmlData )
	{
		// Strip XML declaration
		$xmlData = preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xmlData );

		// Strip xmvc:root
		$xmlData = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xmlData );
		$xmlData = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xmlData );

		return( $xmlData );
	}

	function NormalizeName( $name )
	{
		$name = ucfirst( strtolower( preg_replace( "/\-|_| |\.|%20/", "", $name ) ) );

		return( $name );
	}

	function OutputHeaders( $outputType )
	{
		switch( strtoupper( $outputType ) )
		{
			case "HTML":
				xMVC::OutputHTMLHeaders();
			break;

			case "XML":
			default:
				xMVC::OutputXMLHeaders();
		}
	}

	function OutputXMLHeaders()
	{
		header( "Content-type: application/xml; charset=UTF-8" );

		xMVC::OutputNoCacheHeaders();
	}

	function OutputHTMLHeaders()
	{
		header( "Content-type: text/html; charset=UTF-8" );					// ideally the Content-type would be application/xhtml+xml

		xMVC::OutputNoCacheHeaders();
	}

	function OutputNoCacheHeaders()
	{
		header( "Expires: Mon, 14 Oct 2002 05:00:00 GMT" );					// Date in the past
		header( "Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . " GMT" );	// Always modified
		header( "Cache-Control: no-store, no-cache, must-revalidate" );		// HTTP 1.1
		header( "Cache-Control: post-check=0, pre-check=0", false );
		header( "Pragma: no-cache" );										// HTTP 1.0
	}

	function InstantiateRootController()
	{
		list( $controllerNameRouted, $controllerNameOriginal ) = xMVC::Controller();

		// Check to see if the original non-routed controller is available as a sys controller.  If it exists, it should load above all else.

		$systemControllerFile = SYS_PATH . "controllers/" . strtolower( $controllerNameOriginal ) . ".php";

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

			$applicationControllerFile	= APP_PATH . "controllers/" . strtolower( $controllerName ) . ".php";
			$systemControllerFile		= SYS_PATH . "controllers/" . strtolower( $controllerName ) . ".php";

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

			$controllerClassName = xMVC::NormalizeName( $controllerName );

			$controllerInstance = new $controllerClassName;

			$pathData	= xMVC::PathData();
			$pathParts	= $useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

			if( count( $pathParts ) <= 1 )
			{
				$controllerInstance->Index();
			}
			else
			{
				$method = xMVC::NormalizeName( $pathParts[ 1 ] );

				if( method_exists( $controllerInstance, $method ) && substr( $method, 0, 2 ) !== "__" )
				{
					$args = array_slice( $pathParts, 2 );

					array_walk( $args, create_function( '&$value, $key', '$value = ( "\"" . $value . "\"" );' ) );

					eval( "\$controllerInstance->\$method( " . implode( ", ", $args ) . " );" );
				}
				else
				{
					xMVC::InvokeError( "error.http", "en.errors.http", array( "errorCode" => "404", "method" => $method ) );
				}
			}
		}
		else
		{
			xMVC::InvokeError( "error.http", "en.errors.http", array( "errorCode" => "404", "controllerFile" => $controllerFile ) );
		}
	}

	function InvokeError( $viewName = "error.http", $modelName = "en.errors.http", $data = array() )
	{
		$model = new Model( "xml" );
		$model->xml->Load( $modelName );

		$view = new View();
		$view->PushModel( $model );
		$view->Render( $viewName, array_merge( $data, array( "model" => $model ) ) );

		die();
	}

	// Private methods

	function __DetermineURI()
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

	function __DeterminePathData( $URI )
	{
		$routedURI = $URI;

		// Apply routing rules

		$routes = xMVC::Routes();

		if( isset( $routes ) && is_array( $routes ) )
		{
			if( Config::Value( "useQueryInRoutes" ) === "false" )
			{
				$routedURI = preg_replace( "/\?.*$/", "", $routedURI );
			}

			foreach( $routes as $preg => $replace )
			{
				if( preg_match( $preg, $routedURI, $routeMatches ) )
				{
					xMVC::__RouteMatches( $routeMatches );

					$routedURI = preg_replace_callback( "/\\$([0-9]+)/", array( xMVC, "__RouteReplaceCallback" ), $replace );

					break;
				}
			}
		}

		$pathOnlyOriginal = substr( $URI, 0, strpos( ( strpos( $URI, "?" ) === false ? ( $URI . "?" ) : $URI ), "?" ) );

		if( $pathOnlyOriginal != "/" )
		{
			$pathOnlyOriginal = ( substr( $pathOnlyOriginal, 0, 1 ) == "/" ? substr( $pathOnlyOriginal, 1 ) : $pathOnlyOriginal );
			$pathOnlyOriginal = ( substr( $pathOnlyOriginal, -1 ) == "/" ? substr( $pathOnlyOriginal, 0, -1 ) : $pathOnlyOriginal );
		}
		else
		{
			$pathOnlyOriginal = "";
		}

		$pathOnly = substr( $routedURI, 0, strpos( ( strpos( $routedURI, "?" ) === false ? ( $routedURI . "?" ) : $routedURI ), "?" ) );

		if( $pathOnly != "/" )
		{
			$pathOnly = ( substr( $pathOnly, 0, 1 ) == "/" ? substr( $pathOnly, 1 ) : $pathOnly );
			$pathOnly = ( substr( $pathOnly, -1 ) == "/" ? substr( $pathOnly, 0, -1 ) : $pathOnly );
		}
		else
		{
			$pathOnly = "";
		}

		$pathPartsOriginal	= explode( "/", $pathOnlyOriginal );
		$pathParts			= explode( "/", $pathOnly );

		$pathData = array();

		$pathData[ "pathOnlyOriginal" ]		= $pathOnlyOriginal;
		$pathData[ "pathPartsOriginal" ]	= $pathPartsOriginal;
		$pathData[ "pathOnly" ]				= $pathOnly;
		$pathData[ "pathParts" ]			= $pathParts;

		return( $pathData );
	}

	function __RouteReplaceCallback( $matches )
	{
		$index = $matches[ 1 ];

		$routeMatches = xMVC::__RouteMatches();

		return( $routeMatches[ $index ] );
	}

	function __DetermineController( $useRoutes = true )
	{
		$pathData	= xMVC::PathData();
		$pathParts	= $useRoutes ? $pathData[ "pathParts" ] : $pathData[ "pathPartsOriginal" ];

		if( $pathParts[ 0 ] != "" )
		{
			$controller = $pathParts[ 0 ];
		}
		else
		{
			$controller = xMVC::DefaultController();
		}

		return( $controller );
	}

	function __RouteMatches( $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue = $value;
		}

		return( $storedValue );
	}
}

?>