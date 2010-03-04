<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Routing;
use xMVC\Sys\Config;
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
		$links = array();

		foreach( glob( "app/" . Loader::modelFolder . "/instances/*/*.xml" ) as $file )
		{
			$model = new XMLModelDriver( $file );

			foreach( $model->xPath->query( "//meta:href" ) as $hrefNode )
			{
				$name = $model->xPath->query( "ancestor::instance:*/@name", $hrefNode )->item( 0 )->nodeValue;
				$lang = $hrefNode->getAttribute( "lang" );
				$parent = $model->xPath->query( "../meta:parent", $hrefNode )->item( 0 )->nodeValue;
				$component = $model->xPath->query( "ancestor::instance:*", $hrefNode )->item( 0 )->localName;

				$links[ $lang ][ $name ] = array(
					"path" => $hrefNode->nodeValue,
					"parent" => $parent,
					"file" => $file,
					"component" => $component
				);
			}
		}

		return( $links );
	}

	private static function GenerateSitemapXMLForLanguage( $lang, $linkData )
	{
		$sitemapModel = new XMLModelDriver();

		$urlsetNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "urlset" );
		$sitemapModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $urlsetNode );

		$urlsetNode->setAttributeNS( "http://www.w3.org/2000/xmlns/", "xmlns:sitemap", Config::$data[ "ccNamespaces" ][ "sitemap" ] );

		foreach( $linkData as $name => $data )
		{
			$path = $data[ "path" ];
			$parent = $data[ "parent" ];
			$file = $data[ "file" ];
			$component = $data[ "component" ];

			$urlNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "url" );
			$urlsetNode->appendChild( $urlNode );

			$loc = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . $path;
			$locNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "loc", $loc );
			$urlNode->appendChild( $locNode );

			$lastMod = date( "Y-m-d", filemtime( $file ) );
			$lastModNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "lastmod", $lastMod );
			$urlNode->appendChild( $lastModNode );

			$nameNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:name", $name );
			$urlNode->appendChild( $nameNode );

			if( strlen( $parent ) > 0 )
			{
				$parentNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:parent", $parent );
				$urlNode->appendChild( $parentNode );
			}

			$pathNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:path", $path );
			$urlNode->appendChild( $pathNode );

			$pathNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:component", $component );
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
		$pathOnlyOriginal = Routing::GetPathOnlyOriginal();
		$currentPath = "/" . ( strlen( $pathOnlyOriginal ) > 0 ? $pathOnlyOriginal . "/" : "" );

		return( self::GetPageNameByPath( $currentPath ) );
	}

	public static function GetPageNameByPath( $path )
	{
		foreach( Language::GetDefinedLangs() as $lang )
		{
			$sitemapModel = self::Get( $lang );

			foreach( $sitemapModel->xPath->query( "//s:url[ sitemap:path = '" . $path . "' ]" ) as $urlNode )
			{
				$pageName = $sitemapModel->xPath->query( "sitemap:name", $urlNode )->item( 0 )->nodeValue;

				return( $pageName );
			}
		}

		return( false );
	}

	public static function GetLinkDataFromSitemapByPath( $path )
	{
		foreach( Language::GetDefinedLangs() as $lang )
		{
			$sitemapModel = self::Get( $lang );

			foreach( $sitemapModel->xPath->query( "//s:url[ sitemap:path = '" . $path . "' ]" ) as $urlNode )
			{
				$linkData = array();
				$linkData[ "name" ] = $sitemapModel->xPath->query( "sitemap:name", $urlNode )->item( 0 )->nodeValue;
				$linkData[ "path" ] = $sitemapModel->xPath->query( "sitemap:path", $urlNode )->item( 0 )->nodeValue;
				$linkData[ "component" ] = $sitemapModel->xPath->query( "sitemap:component", $urlNode )->item( 0 )->nodeValue;
				$linkData[ "lang" ] = $lang;

				return( $linkData );
			}
		}

		return( false );
	}

	public static function GetPathByPageNameAndLanguage( $name, $lang )
	{
		$sitemapModel = self::Get( $lang );

		$path = $sitemapModel->xPath->query( "//s:url/sitemap:path[ ../sitemap:name = '" . $name . "' ]" )->item( 0 )->nodeValue;

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