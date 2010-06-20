<?php

namespace Modules\WebCommon\Libraries;

use System\Libraries\Routing;
use System\Libraries\OutputHeaders;
use System\Drivers\XMLModelDriver;
use System\Libraries\Config;
use System\Libraries\Normalize;
use System\Libraries\OverrideableSingleton;
use Modules\Language\Libraries\Language;
use Modules\Utils\Libraries\StringUtils;
use Modules\WebWiredoc\Libraries\Components\ComponentLookup;

class Sitemap extends OverrideableSingleton
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

		$hrefNodeList = $lookupModel->xPath->query( "//lookup:entry/lookup:href[ php:function( 'Modules\Language\Libraries\Language::XSLTLang', '" . $lang . "', (ancestor-or-self::*/@xml:lang)[last()] ) and lookup:private = '0' ]" );

		foreach( $hrefNodeList as $hrefNode )
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
		$definedLangs = Language::GetDefinedLangs();

		foreach( $definedLangs as $lang )
		{
			$filenames[] = StringUtils::ReplaceTokensInPattern( Config::$data[ "sitemapXMLFilePattern" ], array( "protocol" => Routing::URIProtocol(), "host" => $_SERVER[ "HTTP_HOST" ], "lang" => $lang ) );
		}

		return $filenames;
	}

	public static function ReplacePageNameTokensWithPath()
	{
		$routeGroups = array( "routes", "priorityRoutes", "lowPriorityRoutes" );

		foreach( $routeGroups as $routeGroup )
		{
			$routeGroupKeys = array_keys( Config::$data[ $routeGroup ] );

			foreach( $routeGroupKeys as $pattern )
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