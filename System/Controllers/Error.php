<?php

namespace System\Controllers;

use System\Libraries\ErrorHandler;

class Error
{
	public function index( $errorCode = "404" )
	{
		self::display( $errorCode );
	}

	public function display( $errorCode = "404" )
	{
		ErrorHandler::invokeHTTPError( array( "errorCode" => $errorCode, "controllerFile" => "N/A", "method" => "N/A" ) );
	}
}