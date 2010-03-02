<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\OutputHeaders;
use xMVC\Sys\Config;

class Robotstxt
{
	public function Index()
	{
		OutputHeaders::Custom( "Content-type: text/plain; charset=UTF-8" );

		if( $this->DoNotSpider() )
		{
			echo( "User-agent: *\n" );
			echo( "Disallow: /\n" );
		}

		if( Config::$data[ "includeSitemaps" ] )
		{
			$this->WriteSitemapLines();
		}
	}

	private function DoNotSpider()
	{
		if( preg_match( Config::$data[ "noSpiderHostMatch" ], $_SERVER[ "HTTP_HOST" ] ) !== false )
		{
			return( true );
		}

		return( false );
	}

	private function WriteSitemapLines()
	{
		foreach( Sitemap::GetSitemapXMLFilenames() as $filename )
		{
			echo( "Sitemap: " . $filename . "\n" );
		}
	}
}

?>