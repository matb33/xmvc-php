<?php

namespace xMVC\Mod\Favicon;

class Favicon
{
	private $favIconFolder;
	
	public function Index()
	{
		$this->favIconFolder =  "./app/inc/images/";
		$this->Ico();
	}

	public function Ico()
	{
		if( file_exists( $this->favIconFolder . "favicon.ico" ) )
		{
			header( "Content-type: image/vnd.microsoft.icon" );
			echo( file_get_contents( $this->favIconFolder . "favicon.ico" ) );
		}
	}

	public function Gif()
	{
		if( file_exists( $this->favIconFolder . "favicon.gif" ) )
		{
			header( "Content-type: image/gif" );
			echo( file_get_contents( $this->favIconFolder . "favicon.gif" ) );
		}
	}

	public function Png()
	{
		if( file_exists( $this->favIconFolder . "favicon.png" ) )
		{
			header( "Content-type: image/png" );
			echo( file_get_contents( $this->favIconFolder . "favicon.png" ) );
		}
	}
}