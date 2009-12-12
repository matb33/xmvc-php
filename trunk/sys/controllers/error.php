<?php

namespace xMVC;

class Error
{
	public static function Index( $errorCode = "404" )
	{
		self::Display( $errorCode );
	}

	public static function Display( $errorCode = "404" )
	{
		ErrorHandler::InvokeHTTPError( array( "errorCode" => $errorCode, "controllerFile" => "N/A", "method" => "N/A" ) );
	}
}

?>