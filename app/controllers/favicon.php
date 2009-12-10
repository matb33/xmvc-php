<?php

class Favicon
{
	public function Index()
	{
		$this->Ico();
	}

	public function Ico()
	{
		header( "Content-type: image/vnd.microsoft.icon" );
		echo( file_get_contents( "./inc/images/favicon.ico" ) );
	}

	public function Gif()
	{
		header( "Content-type: image/gif" );
		echo( file_get_contents( "./inc/images/favicon.gif" ) );
	}

	public function Png()
	{
		header( "Content-type: image/png" );
		echo( file_get_contents( "./inc/images/favicon.png" ) );
	}
}

?>