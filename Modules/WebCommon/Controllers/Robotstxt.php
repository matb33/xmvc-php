<?php

namespace Modules\WebCommon\Controllers;

use System\Libraries\OutputHeaders;
use System\Libraries\Config;
use Modules\Language\Libraries\Language;
use Modules\WiredocPHP\Libraries\Components\ComponentLookup;

class Robotstxt
{
	public function __construct()
	{
	}

	public function index()
	{
		OutputHeaders::custom( "Content-type: text/plain; charset=UTF-8" );

		echo( "User-agent: *\n" );

		if( $this->doNotSpider() )
		{
			echo( "Disallow: /\n" );
		}
		else
		{
			if( Config::$data[ "includeSitemapDisallows" ] )
			{
				$this->writeSitemapDisallows();
			}
		}

		if( Config::$data[ "includeSitemaps" ] )
		{
			$this->writeSitemapLines();
		}
	}

	private function doNotSpider()
	{
		if( preg_match( Config::$data[ "noSpiderHostMatch" ], $_SERVER[ "HTTP_HOST" ] ) )
		{
			return true;
		}

		return false;
	}

	private function writeSitemapDisallows()
	{
		$lookupModel = ComponentLookup::getInstance()->get();
		$hrefNodeList = $lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:private = '1' ]" );

		foreach( $hrefNodeList as $hrefNode )
		{
			$locNodeList = $lookupModel->xPath->query( "lookup:fully-qualified-uri", $hrefNode );
			$loc = $locNodeList->length > 0 ? $locNodeList->item( 0 )->nodeValue : "";

			echo( "Disallow: " . $loc . "\n" );
		}
	}

	private function writeSitemapLines()
	{
		$filenames = Sitemap::getInstance()->getSitemapXMLFilenames();

		foreach( $filenames as $filename )
		{
			echo( "Sitemap: " . $filename . "\n" );
		}
	}
}