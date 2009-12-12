<?php

namespace xMVC;

class Favicon
{
	public static function Index()
	{
		self::Ico();
	}

	public static function Ico()
	{
		header( "Content-type: image/vnd.microsoft.icon" );
		echo( file_get_contents( "./inc/images/favicon.ico" ) );
	}

	public static function Gif()
	{
		header( "Content-type: image/gif" );
		echo( file_get_contents( "./inc/images/favicon.gif" ) );
	}

	public static function Png()
	{
		header( "Content-type: image/png" );
		echo( file_get_contents( "./inc/images/favicon.png" ) );
	}
}

?>