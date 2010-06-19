<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\Routing;
use xMVC\Sys\OutputHeaders;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Config;
use xMVC\Sys\Normalize;
use xMVC\Sys\Singleton;
use xMVC\Mod\Language\Language;
use xMVC\Mod\Utils\StringUtils;
use xMVC\Mod\WireKit\Components\ComponentLookup;

class Sitemap extends Singleton
{
	public function GetCurrentFullyQualifiedPageName()
	{
		$pathOnlyOriginal = Routing::GetPathOnlyOriginal();
		$currentPath = "/" . ( strlen( $pathOnlyOriginal ) > 0 ? $pathOnlyOriginal . "/" : "" );

		return ComponentLookup::getInstance()->GetFullyQualifiedNameByPath( $currentPath );
	}

	public function Output( $lang = null )
	{
		if( is_null( $lang ) )
		{
			$lang = Language::GetLang();
		}

		OutputHeaders::XML();

		$lookupModel = ComponentLookup::getInstance()->Get();

		$sitemapModel = new XMLModelDriver();
		$sitemapModel->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );

		$urlsetNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "urlset" );
		$sitemapModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $urlsetNode );

		foreach( $lookupModel->xPath->query( "//lookup:entry/lookup:href[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', '" . $lang . "', (ancestor-or-self::*/@xml:lang)[last()] ) and lookup:private = '0' ]" ) as $hrefNode )
		{
			$urlNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "url" );
			$urlsetNode->appendChild( $urlNode );

			$locNodeList = $lookupModel->xPath->query( "lookup:fully-qualified-uri", $hrefNode );
			$loc = $locNodeList->length > 0 ? $locNodeList->item( 0 )->nodeValue : "";
			$locNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "loc", $loc );
			$urlNode->appendChild( $locNode );

			$lastModNodeList = $lookupModel->xPath->query( "../lookup:modified", $hrefNode );
			$lastMod = $lastModNodeList->length > 0 ? date( "Y-m-d", strtotime( $lastModNodeList->item( 0 )->nodeValue ) ) : "";
			$lastModNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "lastmod", $lastMod );
			$urlNode->appendChild( $lastModNode );
		}

		echo( $sitemapModel->saveXML());
	}

	public function GetSitemapXMLFilenames()
	{
		$filenames = array();

		foreach( Language::GetDefinedLangs() as $lang )
		{
			$filenames[] = StringUtils::ReplaceTokensInPattern( Config::$data[ "sitemapXMLFilePattern" ], array( "protocol" => Routing::URIProtocol(), "host" => $_SERVER[ "HTTP_HOST" ], "lang" => $lang ) );
		}

		return $filenames;
	}

	public static function ReplacePageNameTokensWithPath()
	{
		foreach( array( "routes", "priorityRoutes", "lowPriorityRoutes" ) as $routeGroup )
		{
			foreach( array_keys( Config::$data[ $routeGroup ] ) as $pattern )
			{
				preg_match_all( "|#([A-Za-z0-9-_/.]+)#|", $pattern, $matches );

				if( count( $matches[ 0 ] ) )
				{
					$updatedPattern = $pattern;

					foreach( $matches[ 0 ] as $key => $match )
					{
						$path = ComponentLookup::getInstance()->GetPathByFullyQualifiedNameAndLanguage( $matches[ 1 ][ $key ], Language::GetLang() );
						$updatedPattern = str_replace( $match, addcslashes( $path, "/" ), $updatedPattern );
					}

					Config::$data[ $routeGroup ][ $updatedPattern ] = Config::$data[ $routeGroup ][ $pattern ];
					unset( Config::$data[ $routeGroup ][ $pattern ] );
				}
			}
		}
	}
}