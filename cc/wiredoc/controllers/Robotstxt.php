<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\OutputHeaders;
use xMVC\Sys\Config;
use xMVC\Mod\Language\Language;

class Robotstxt
{
	public function Index()
	{
		OutputHeaders::Custom( "Content-type: text/plain; charset=UTF-8" );

		echo( "User-agent: *\n" );

		if( $this->DoNotSpider() )
		{
			echo( "Disallow: /\n" );
		}
		else
		{
			$this->WriteSitemapDisallows();
		}

		if( Config::$data[ "includeSitemaps" ] )
		{
			$this->WriteSitemapLines();
		}
	}

	private function DoNotSpider()
	{
		if( preg_match( Config::$data[ "noSpiderHostMatch" ], $_SERVER[ "HTTP_HOST" ] ) )
		{
			return( true );
		}

		return( false );
	}

	private function WriteSitemapDisallows()
	{
		foreach( Language::GetDefinedLangs() as $lang )
		{
			$sitemapModel = Sitemap::Get( $lang );

			foreach( $sitemapModel->xPath->query( "//s:url[ sitemap:private = '1' ]/sitemap:path" ) as $pathNode )
			{
				echo( "Disallow: " . $pathNode->nodeValue . "\n" );
			}
		}
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