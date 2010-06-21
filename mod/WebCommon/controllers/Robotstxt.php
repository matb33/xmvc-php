<?php

namespace xMVC\Mod\WebCommon;

use xMVC\Sys\OutputHeaders;
use xMVC\Sys\Config;
use xMVC\Mod\Language\Language;
use xMVC\Mod\WiredocPHP\Components\ComponentLookup;

class Robotstxt
{
	public function __construct()
	{
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
			return true;
		}

		return false;
	}

	private function WriteSitemapDisallows()
	{
		$lookupModel = ComponentLookup::getInstance()->Get();

		foreach( $lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:private = '1' ]" ) as $hrefNode )
		{
			$locNodeList = $lookupModel->xPath->query( "lookup:fully-qualified-uri", $hrefNode );
			$loc = $locNodeList->length > 0 ? $locNodeList->item( 0 )->nodeValue : "";

			echo( "Disallow: " . $loc . "\n" );
		}
	}

	private function WriteSitemapLines()
	{
		foreach( Sitemap::getInstance()->GetSitemapXMLFilenames() as $filename )
		{
			echo( "Sitemap: " . $filename . "\n" );
		}
	}
}