<?php

class Contactus extends Controller
{
	function Contactus()
	{
		parent::Controller();
	}

	function Index()
	{
		$data = array( "controllerName" => strtolower( __CLASS__ ) );

		$commonContent = new Model( "xml" );
		$commonContent->xml->Load( "en.common.content" );

		$pageContent = new Model( "xml" );
		$pageContent->xml->Load( "en.contact-us.content" );

		$formContent = new Model( "xml" );
		$formContent->xml->Load( "en.form.content" );

		$page = new View();
		$page->PushModel( $commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $formContent );
		$page->Render( "contact-us", $data );
	}

	function Send()
	{
		$xmlData = strlen( $_POST[ "d" ] ) > 0 ? base64_decode( $_POST[ "d" ] ) : "<null />";

		$data = new Model( "xml" );
		$data->xml->Load( $xmlData );

		$content = new Model( "xml" );
		$content->xml->Load( "en.contact-us.content" );

		$formContent = new Model( "xml" );
		$formContent->xml->Load( "en.form.content" );

		//$success = PutSubmission( $content, $formContent, $data );

		$results = new Model( "xml" );
		$results->xml->Load( "<root><success>" . ( $success ? "1" : "0" ) . "</success></root>" );

		$page = new View();
		$page->PushModel( $results );
		$page->PassThru();
	}

	function Thanks()
	{
		$data = array( "controllerName" => strtolower( __CLASS__ ) );

		$commonContent = new Model( "xml" );
		$commonContent->xml->Load( "en.common.content" );

		$pageContent = new Model( "xml" );
		$pageContent->xml->Load( "en.contact-us-thanks.content" );

		$page = new View();
		$page->PushModel( $commonContent );
		$page->PushModel( $pageContent );
		$page->Render( "contact-us-thanks", $data );
	}
}

?>