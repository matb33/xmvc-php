<?php

class Favicon extends Controller
{
	function Favicon()
	{
		parent::Controller();
	}

	function Index()
	{
		$this->ico();
	}

	function Ico()
	{
		header( "Content-type: image/vnd.microsoft.icon" );
		echo( file_get_contents( "./inc/images/favicon.ico" ) );
	}

	function Gif()
	{
		header( "Content-type: image/gif" );
		echo( file_get_contents( "./inc/images/favicon.gif" ) );
	}

	function Png()
	{
		header( "Content-type: image/png" );
		echo( file_get_contents( "./inc/images/favicon.png" ) );
	}
}

?>