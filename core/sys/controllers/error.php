<?php
class Error extends Controller
{
	var $errorCode		= null;
	var $errorMessages	= null;
	var $language		= "en";
	
	function Error()
	{
		parent::Controller();
		
		$this->errorMessages = new Model( "xml" );
		$this->errorMessages->xml->Load( $this->language . ".errors.http" );
	}

	function Index( $error = "404" )
	{
		$this->Display( $error );
	}
	
	function Display( $error = "404" )
	{	
		
		$page = new View();
		$page->PushModel( $this->errorMessages );
		$page->Render( "error.http", array( "errorCode" => $error, "model" => $this->errorMessages ) );
	}
}

?>