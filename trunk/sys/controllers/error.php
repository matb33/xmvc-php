<?php

namespace xMVC;

class Error
{
	public function Index( $errorCode = "404" )
	{
		$this->Display( $errorCode );
	}

	public function Display( $errorCode = "404" )
	{
		ErrorHandler::InvokeHTTPError( array( "errorCode" => $errorCode, "controllerFile" => "N/A", "method" => "N/A" ) );
	}
}

?>