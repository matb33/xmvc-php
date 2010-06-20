<?php

namespace System\Libraries;

use System\Drivers\XMLModelDriver;
use System\Drivers\StringsModelDriver;

use ErrorException;

class ErrorHandler
{
	public static function ExceptionErrorHandler( $errno, $errstr, $errfile, $errline )
	{
		throw new ErrorException( $errstr, 0, $errno, $errfile, $errline );
	}

	public static function UncaughtExceptionHandler( $exception )
	{
		$traceline = "#%s %s(%s): %s(%s)";
		$msg = "PHP Fatal error: Uncaught exception '%s' with message '%s' in %s:%s\nStack trace:\n%s\nthrown in %s on line %s";

		$trace = $exception->getTrace();
		foreach( $trace as $key => $stackPoint )
		{
			$trace[ $key ][ "args" ] = array_map( "gettype", $trace[ $key ][ "args" ] );
		}

		$result = array();
		foreach( $trace as $key => $stackPoint )
		{
			$file = isset( $stackPoint[ "file" ] ) ? $stackPoint[ "file" ] : "file N/A";
			$line = isset( $stackPoint[ "line" ] ) ? $stackPoint[ "line" ] : "line N/A";
			$function = isset( $stackPoint[ "function" ] ) ? $stackPoint[ "function" ] : "function N/A";
			$args = isset( $stackPoint[ "args" ] ) && is_array( $stackPoint[ "args" ] ) ? $stackPoint[ "args" ] : array();

			$result[] = sprintf(
				$traceline,
				$key,
				$file,
				$line,
				$function,
				implode( ", ", $args )
			);
		}

		// trace always ends with {main}
		$result[] = "#" . ++$key . " {main}";

		$logMsg = sprintf(
			$msg,
			get_class( $exception ),
			$exception->getMessage(),
			$exception->getFile(),
			$exception->getLine(),
			implode( "\n", $result ),
			$exception->getFile(),
			$exception->getLine()
		);

		$echoMsg = sprintf(
			$msg,
			"<span style='color:red;font-weight:bold;'>" . get_class( $exception ) . "</span>",
			"<span style='color:blue;font-weight:bold;'>" . $exception->getMessage() . "</span>",
			"<span style='color:green;'>" . $exception->getFile() . "</span>",
			"<b>" . $exception->getLine() . "</b>",
			implode( "\n", $result ),
			"<span style='color:green;'>" . $exception->getFile() . "</span>",
			"<b>" . $exception->getLine() . "</b>"
		);

		error_log( $logMsg );

		echo "<pre>" . $echoMsg . "</pre>";
	}

	/*
	private static $oldErrorHandler;
	private static $errorReporting;
	private static $errorTypes;
	private static $errors;

	public static function HandleErrors()
	{
		//self::$oldErrorHandler = set_error_handler( array( __NAMESPACE__ . "\\ErrorHandler", "ErrorHandlerPHP" ) );
		self::$errorReporting = error_reporting();
		self::$errors = "";

		self::$errorTypes = array(

			E_ERROR				=> "Error",
			E_WARNING			=> "Warning",
			E_PARSE				=> "Parsing Error",
			E_NOTICE			=> "Notice",
			E_CORE_ERROR		=> "Core Error",
			E_CORE_WARNING		=> "Core Warning",
			E_COMPILE_ERROR		=> "Compile Error",
			E_COMPILE_WARNING	=> "Compile Warning",
			E_USER_ERROR		=> "User Error",
			E_USER_WARNING		=> "User Warning",
			E_USER_NOTICE		=> "User Notice",
			E_STRICT			=> "Runtime Notice",
			E_RECOVERABLE_ERROR	=> "Catchable Fatal Error"

		);
	}
	*/

	public static function InvokeHTTPError( $data = array() )
	{
		$headerPattern = "HTTP/1.0 #errorCode# #headerType#";

		self::InvokeError( Config::$data[ "httpErrorView" ], Config::$data[ "httpErrorModel" ], $headerPattern, $data );
	}

	private static function InvokeError( $viewName, $modelName, $headerPattern, $data )
	{
		$model = new XMLModelDriver( $modelName );

		$data[ "headerType" ] = $model->xPath->query( "//xmvc:error[ @code = '" . $data[ "errorCode" ] . "' ]/@type" )->item( 0 )->nodeValue;

		$header = self::CreateHeaderUsingPattern( $headerPattern, $data );

		$strings = new StringsModelDriver();

		if( isset( $data[ "errorCode" ] ) )
		{
			$strings->Add( "error-code", $data[ "errorCode" ] );
		}

		if( isset( $data[ "controllerFile" ] ) )
		{
			$strings->Add( "controller-file", $data[ "controllerFile" ] );
		}

		if( isset( $data[ "method" ] ) )
		{
			$strings->Add( "method", is_string( $data[ "method" ] ) ? $data[ "method" ] : print_r( $data[ "method" ], true ) );
		}

		$view = new View( $viewName );
		$view->PushModel( $model );
		$view->PushModel( $strings );
 		$view->Render( null, $header );

		die();
	}

	private static function CreateHeaderUsingPattern( $pattern, $data )
	{
		foreach( $data as $key => $value )
		{
			if( is_array( $value ) || is_object( $value ) )
			{
				$value = serialize( $value );
			}

			$pattern = str_replace( "#" . $key . "#", $value, $pattern );
		}

		return $pattern;
	}

	/*
	public static function ErrorHandlerXML( $errorNumber, $errorMessage, $filename, $lineNum, $vars )
	{
		$errorException = new \ErrorException( $errorMessage, 0, $errorNumber, $filename, $lineNum );

		$errorXML = "";

		if( ( $errorNumber & self::$errorReporting ) == $errorNumber )
		{
			$errorXML .= "<xmvc:errorentry>";
			$errorXML .= "<xmvc:datetime><![CDATA[" . date( "Y-m-d H:i:s" ) . "]]></xmvc:datetime>";
			$errorXML .= "<xmvc:errornum><![CDATA[" . $errorNumber . "]]></xmvc:errornum>";
			$errorXML .= "<xmvc:errortype><![CDATA[" . self::$errorTypes[ $errorNumber ] . "]]></xmvc:errortype>";
			$errorXML .= "<xmvc:errormsg><![CDATA[" . $errorMessage . "]]></xmvc:errormsg>";
			$errorXML .= "<xmvc:scriptname><![CDATA[" . $filename . "]]></xmvc:scriptname>";
			$errorXML .= "<xmvc:scriptlinenum><![CDATA[" . $lineNum . "]]></xmvc:scriptlinenum>";
			$errorXML .= "<xmvc:stack-trace><![CDATA[" . $errorException->getTraceAsString() . "]]></xmvc:stack-trace>";
			$errorXML .= "</xmvc:errorentry>";
		}

		self::$errors .= $errorXML;

		return true;
	}

	public static function GetErrorsAsXML()
	{
		$errors = trim( self::$errors );

		if( strlen( $errors ) > 0 )
		{
			$errors = "<xmvc:errors>" . $errors . "</xmvc:errors>";
		}

		return $errors;
	}

	public static function ErrorHandlerPHP( $errorNumber, $errorMessage, $filename, $lineNum, $vars )
	{
		$errorException = new \ErrorException( $errorMessage, 0, $errorNumber, $filename, $lineNum );

		if( ( $errorNumber & self::$errorReporting ) == $errorNumber )
		{
			echo( "[" . date( "Y-m-d H:i:s" ) . "] " . self::$errorTypes[ $errorNumber ] . ": " . $errorMessage . ". Line " . $lineNum . " in " . $filename ."<br />\n" . str_replace( "\n", "<br />\n", $errorException->getTraceAsString() ) ) . "<br />\n";
		}

		return true;
	}
	*/
}