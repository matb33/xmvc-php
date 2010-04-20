<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\OutputHeaders;
use xMVC\Sys\Config;
use xMVC\Mod\Language\Language;

class Robotstxt
{
	private $lookup;
	private $sitemap;

	public function __construct()
	{
		$this->lookup = new ComponentLookup();
		$this->sitemap = new Sitemap( $this->lookup->Get() );
	}

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
			if( Config::$data[ "includeSitemapDisallows" ] )
			{
				$this->WriteSitemapDisallows();
			}
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
		$lookupModel = $this->lookup->Get();

		foreach( $lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:private = '1' ]" ) as $hrefNode )
		{
			$locNodeList = $lookupModel->xPath->query( "lookup:fully-qualified-uri", $hrefNode );
			$loc = $locNodeList->length > 0 ? $locNodeList->item( 0 )->nodeValue : "";

			echo( "Disallow: " . $loc . "\n" );
		}
	}

	private function WriteSitemapLines()
	{
		foreach( $this->sitemap->GetSitemapXMLFilenames() as $filename )
		{
			echo( "Sitemap: " . $filename . "\n" );
		}
	}
}

?>