<?php

class ErrorHandler
{
	private static $oldErrorHandler;
	private static $errorReporting;
	private static $errorTypes;
	private static $errors;

	public static function HandleErrors()
	{
		self::$oldErrorHandler = set_error_handler( array( "ErrorHandler", "ErrorHandlerXML" ) );
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

	public static function InvokeError( $viewName = "http-error", $modelName = "http-errors", $data = array() )
	{
		$model = new Model( "xml" );
		$model->xml->Load( $modelName );

		$view = new View();
		$view->PushModel( $model );
		$view->Render( $viewName, array_merge( $data, array( "model" => $model ) ) );

		die();
	}

	public static function ErrorHandlerXML( $errorNumber, $errorMessage, $filename, $lineNum, $vars )
	{
		$err = "";

		if( ( $errorNumber & self::$errorReporting ) == $errorNumber )
		{
			$err .= "<xmvc:errorentry>";
			$err .= "<xmvc:datetime><![CDATA[" . date( "Y-m-d H:i:s (T)" ) . "]]></xmvc:datetime>";
			$err .= "<xmvc:errornum><![CDATA[" . $errorNumber . "]]></xmvc:errornum>";
			$err .= "<xmvc:errortype><![CDATA[" . self::$errorTypes[ $errorNumber ] . "]]></xmvc:errortype>";
			$err .= "<xmvc:errormsg><![CDATA[" . $errorMessage . "]]></xmvc:errormsg>";
			$err .= "<xmvc:scriptname><![CDATA[" . $filename . "]]></xmvc:scriptname>";
			$err .= "<xmvc:scriptlinenum><![CDATA[" . $lineNum . "]]></xmvc:scriptlinenum>";
			$err .= "</xmvc:errorentry>";

		}

		self::$errors .= $err;

		return( true );
	}

	public static function GetErrorsAsXML()
	{
		$errors = trim( self::$errors );

		if( strlen( $errors ) > 0 )
		{
			$errors = "<xmvc:errors>" . $errors . "</xmvc:errors>";
		}

		return( $errors );
	}
}

?>