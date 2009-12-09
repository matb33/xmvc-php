<?php

class Error extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

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