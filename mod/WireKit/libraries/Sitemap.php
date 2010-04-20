<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\Routing;
use xMVC\Sys\OutputHeaders;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Config;
use xMVC\Sys\Normalize;
use xMVC\Mod\Language\Language;
use xMVC\Mod\Utils\StringUtils;

class Sitemap
{
	public $lookupModel;

	public function __construct( &$lookupModel )
	{
		$this->lookupModel = $lookupModel;
	}

	public function GetCurrentFullyQualifiedPageName()
	{
		$pathOnlyOriginal = Routing::GetPathOnlyOriginal();
		$currentPath = "/" . ( strlen( $pathOnlyOriginal ) > 0 ? $pathOnlyOriginal . "/" : "" );

		return( $this->GetFullyQualifiedNameByPath( $currentPath ) );
	}

	public function GetFullyQualifiedNameByPath( $path )
	{
		foreach( $this->lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:uri = '" . $path . "' ]" ) as $entryNode )
		{
			$fullyQualifiedNameNodeList = $this->lookupModel->xPath->query( "../lookup:fully-qualified-name", $entryNode );
			$fullyQualifiedName = $fullyQualifiedNameNodeList->length > 0 ? $fullyQualifiedNameNodeList->item( 0 )->nodeValue : "";

			return( $fullyQualifiedName );
		}

		return( false );
	}

	public function GetLinkDataFromSitemapByPath( $path )
	{
		foreach( $this->lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:uri = '" . $path . "' ]" ) as $entryNode )
		{
			$linkData = array();
			$linkData[ "component" ] = $this->lookupModel->xPath->query( "../lookup:component", $entryNode )->item( 0 )->nodeValue;
			$linkData[ "instanceName" ] = $this->lookupModel->xPath->query( "../lookup:instance-name", $entryNode )->item( 0 )->nodeValue;
			$linkData[ "fullyQualifiedName" ] = $this->lookupModel->xPath->query( "../lookup:fully-qualified-name", $entryNode )->item( 0 )->nodeValue;
			$linkData[ "view" ] = $this->lookupModel->xPath->query( "../lookup:view", $entryNode )->item( 0 )->nodeValue;
			$linkData[ "path" ] = $this->lookupModel->xPath->query( "lookup:uri", $entryNode )->item( 0 )->nodeValue;
			$linkData[ "lang" ] = $this->lookupModel->xPath->query( "lookup:lang", $entryNode )->item( 0 )->nodeValue;

			return( $linkData );
		}

		return( false );
	}

	public function GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $lang )
	{
		$uriNodeList = $this->lookupModel->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $fullyQualifiedName . "' ]/lookup:href[ lang( '" . $lang . "' ) ]/lookup:uri" );
		$path = $uriNodeList->length > 0 ? $uriNodeList->item( 0 )->nodeValue : "";

		return( $path );
	}

	public function Output( $lang = null )
	{
		if( is_null( $lang ) )
		{
			$lang = Language::GetLang();
		}

		OutputHeaders::XML();

		$sitemapModel = new XMLModelDriver();
		$sitemapModel->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );

		$urlsetNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "urlset" );
		$sitemapModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $urlsetNode );

		foreach( $this->lookupModel->xPath->query( "//lookup:entry/lookup:href[ lang( '" . $lang . "' ) and lookup:private = '0' ]" ) as $hrefNode )
		{
			$urlNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "url" );
			$urlsetNode->appendChild( $urlNode );

			$locNodeList = $this->lookupModel->xPath->query( "lookup:fully-qualified-uri", $hrefNode );
			$loc = $locNodeList->length > 0 ? $locNodeList->item( 0 )->nodeValue : "";
			$locNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "loc", $loc );
			$urlNode->appendChild( $locNode );

			$lastModNodeList = $this->lookupModel->xPath->query( "../lookup:modified", $hrefNode );
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

		return( $filenames );
	}

	public static function ReplacePageNameTokensWithPath()
	{
		$lookup = new ComponentLookup();
		$sitemap = new Sitemap( $lookup->Get() );

		foreach( array( "routes", "priorityRoutes", "lowPriorityRoutes" ) as $routeGroup )
		{
			foreach( array_keys( Config::$data[ $routeGroup ] ) as $pattern )
			{
				preg_match_all( "/#([A-Za-z0-9-_\\\\]+)#/", $pattern, $matches );

				if( count( $matches[ 0 ] ) )
				{
					$updatedPattern = $pattern;

					foreach( $matches[ 0 ] as $key => $match )
					{
						$updatedPattern = str_replace( $match, addcslashes( $sitemap->GetPathByFullyQualifiedNameAndLanguage( $matches[ 1 ][ $key ], Language::GetLang() ), "/" ), $updatedPattern );
					}

					Config::$data[ $routeGroup ][ $updatedPattern ] = Config::$data[ $routeGroup ][ $pattern ];
					unset( Config::$data[ $routeGroup ][ $pattern ] );
				}
			}
		}
	}
}

?>