<?php
class Error extends Controller
{
	private $errorCode		= null;
	private $errorMessages	= null;
	private $language		= "en";

	public function __construct()
	{
		parent::__construct();

		$this->errorMessages = new Model( "xml" );
		$this->errorMessages->xml->Load( $this->language . ".errors.http" );
	}

	public function Index( $error = "404" )
	{
		$this->Display( $error );
	}

	public function Display( $error = "404" )
	{
		$page = new View();
		$page->PushModel( $this->errorMessages );
		$page->Render( "error.http", array( "errorCode" => $error, "model" => $this->errorMessages ) );
	}
}

?>