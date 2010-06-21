<?php

namespace Modules\Favicon\Controllers;

class Favicon
{
	private $favIconFolder;

	public function Index()
	{
		$this->favIconFolder =  "./Application/Public/images/";
		$this->ico();
	}

	public function ico()
	{
		if( file_exists( $this->favIconFolder . "favicon.ico" ) )
		{
			header( "Content-type: image/vnd.microsoft.icon" );
			echo( file_get_contents( $this->favIconFolder . "favicon.ico" ) );
		}
	}

	public function gif()
	{
		if( file_exists( $this->favIconFolder . "favicon.gif" ) )
		{
			header( "Content-type: image/gif" );
			echo( file_get_contents( $this->favIconFolder . "favicon.gif" ) );
		}
	}

	public function png()
	{
		if( file_exists( $this->favIconFolder . "favicon.png" ) )
		{
			header( "Content-type: image/png" );
			echo( file_get_contents( $this->favIconFolder . "favicon.png" ) );
		}
	}
}