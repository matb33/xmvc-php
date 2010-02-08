<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Routing;
use xMVC\Sys\Config;
use xMVC\Sys\Core;
use xMVC\Sys\OutputHeaders;

use Module\Language\Language;

class Sitemap
{
	private static $models;

	public static function Generate()
	{
		$links = self::GatherLinksFromModels();

		foreach( $links as $lang => $data )
		{
			$sitemapXML = self::GenerateSitemapXMLForLanguage( $lang, $data );

			self::WriteSitemapXMLForLanguage( $lang, $sitemapXML );
		}
	}

	private static function GatherLinksFromModels()
	{
		foreach( glob( "app/" . Loader::modelFolder . "/*/*.ccx" ) as $ccxFile )
		{
			$ccxModel = new XMLModelDriver( $ccxFile );

			foreach( $ccxModel->xPath->query( "//cc:config/cc:page" ) as $pageNode )
			{
				$name = $pageNode->getAttribute( "name" );
				$parent = $pageNode->getAttribute( "parent" );

				foreach( $ccxModel->xPath->query( "cc:href", $pageNode ) as $linkNode )
				{
					$lang = $linkNode->getAttribute( "lang" );

					$links[ $lang ][ $name ] = array(
						"path" => $linkNode->nodeValue,
						"parent" => $parent,
						"file" => $ccxFile
					);
				}
			}
		}

		return( $links );
	}

	private static function GenerateSitemapXMLForLanguage( $lang, $linkData )
	{
		$sitemapModel = new XMLModelDriver();

		$urlsetNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "urlset" );
		$sitemapModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $urlsetNode );

		$urlsetNode->setAttributeNS( "http://www.w3.org/2000/xmlns/", "xmlns:cc", Config::$data[ "ccNamespace" ] );

		foreach( $linkData as $name => $data )
		{
			$path = $data[ "path" ];
			$parent = $data[ "parent" ];
			$file = $data[ "file" ];

			$urlNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "url" );
			$urlsetNode->appendChild( $urlNode );

			$loc = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . $path;
			$locNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "loc", $loc );
			$urlNode->appendChild( $locNode );

			$lastMod = date( "Y-m-d", filemtime( $file ) );
			$lastModNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "lastmod", $lastMod );
			$urlNode->appendChild( $lastModNode );

			$nameNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespace" ], "cc:name", $name );
			$urlNode->appendChild( $nameNode );

			if( strlen( $parent ) > 0 )
			{
				$parentNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespace" ], "cc:parent", $parent );
				$urlNode->appendChild( $parentNode );
			}

			$pathNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespace" ], "cc:path", $path );
			$urlNode->appendChild( $pathNode );
		}

		$sitemapXML = $sitemapModel->saveXML( $urlsetNode );

		return( $sitemapXML );
	}

	private static function WriteSitemapXMLForLanguage( $lang, $sitemapXML )
	{
		$filename = self::NormalizeSitemapXMLFilePattern( $lang );

		$sitemapXML = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n" . $sitemapXML;

		if( file_put_contents( $filename, $sitemapXML, FILE_TEXT ) )
		{
			return( true );
		}

		return( false );
	}

	public static function Load( $lang )
	{
		self::$models[ $lang ] = null;

		$filename = self::NormalizeSitemapXMLFilePattern( $lang );

		if( ! file_exists( $filename ) )
		{
			self::Generate();
		}

		if( file_exists( $filename ) )
		{
			self::$models[ $lang ] = new XMLModelDriver( $filename );
			self::$models[ $lang ]->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );
		}

		return( self::$models[ $lang ] );
	}

	public static function NormalizeSitemapXMLFilePattern( $lang )
	{
		$filename = str_replace( "#lang#", $lang, Config::$data[ "sitemapXMLFilePattern" ] );
		$filename = str_replace( "#modelFolder#", Loader::modelFolder, $filename );

		return( $filename );
	}

	public static function Get( $lang )
	{
		if( is_null( self::$models[ $lang ] ) )
		{
			return( self::Load( $lang ) );
		}
		else
		{
			return( self::$models[ $lang ] );
		}
	}

	public static function GetCurrentPageName()
	{
		$pathData = Routing::PathData();
		$currentPath = "/" . ( strlen( $pathData[ "pathOnlyOriginal" ] ) > 0 ? $pathData[ "pathOnlyOriginal" ] . "/" : "" );

		foreach( Language::GetDefinedLangs() as $lang )
		{
			$sitemapModel = self::Get( $lang );

			foreach( $sitemapModel->xPath->query( "//s:url[ cc:path = '" . $currentPath . "' ]" ) as $urlNode )
			{
				$pageName = $sitemapModel->xPath->query( "cc:name", $urlNode )->item( 0 )->nodeValue;

				return( $pageName );
			}
		}

		return( false );
	}

	public static function GetPathByPageNameAndLanguage( $name, $lang )
	{
		$sitemapModel = self::Get( $lang );

		$path = $sitemapModel->xPath->query( "//s:url/cc:path[ ../cc:name = '" . $name . "' ]" )->item( 0 )->nodeValue;

		return( $path );
	}

	public static function GetSitemapXMLFilenames()
	{
		$filenames = array();

		foreach( glob( self::NormalizeSitemapXMLFilePattern( "*" ) ) as $filename )
		{
			$filenames[] = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/" . basename( $filename );
		}

		return( $filenames );
	}

	public static function Output( $lang )
	{
		OutputHeaders::XML();

		echo( file_get_contents( self::NormalizeSitemapXMLFilePattern( $lang ) ) );
	}
}

?>