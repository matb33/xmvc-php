<?php

namespace xMVC\Sys;

class Error
{
	public function Index( $errorCode = "404" )
	{
		self::Display( $errorCode );
	}

	public function Display( $errorCode = "404" )
	{
		ErrorHandler::InvokeHTTPError( array( "errorCode" => $errorCode, "controllerFile" => "N/A", "method" => "N/A" ) );
	}
}

?>