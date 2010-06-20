<?php

namespace Modules\WebCommon\Controllers;

use System\Libraries\OutputHeaders;
use System\Libraries\Config;
use Modules\Language\Libraries\Language;
use Modules\WebWiredoc\Libraries\Components\ComponentLookup;

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
		$hrefNodeList = $lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:private = '1' ]" );

		foreach( $hrefNodeList as $hrefNode )
		{
			$locNodeList = $lookupModel->xPath->query( "lookup:fully-qualified-uri", $hrefNode );
			$loc = $locNodeList->length > 0 ? $locNodeList->item( 0 )->nodeValue : "";

			echo( "Disallow: " . $loc . "\n" );
		}
	}

	private function WriteSitemapLines()
	{
		$filenames = Sitemap::getInstance()->GetSitemapXMLFilenames();

		foreach( $filenames as $filename )
		{
			echo( "Sitemap: " . $filename . "\n" );
		}
	}
}