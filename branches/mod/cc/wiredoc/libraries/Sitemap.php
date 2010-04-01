<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Routing;
use xMVC\Sys\Config;
use xMVC\Sys\OutputHeaders;

use xMVC\Mod\Language\Language;

class Sitemap
{
	private static $models;

	public static function Generate()
	{
		$sitemapModels = self::GenerateSitemapModels();

		foreach( $sitemapModels as $lang => $sitemapModel )
		{
			self::WriteSitemapModelForLanguage( $lang, $sitemapModel );
		}
	}

	private static function GenerateSitemapModels()
	{
		$links = self::GatherLinksFromModels();

		$sitemapModels = array();

		foreach( $links as $lang => $data )
		{
			$sitemapModels[ $lang ] = self::GenerateSitemapModelForLanguage( $lang, $data );
		}

		return( $sitemapModels );
	}

	private static function GatherLinksFromModels()
	{
		$links = array();

		$instances = StringUtils::ReplaceTokensInPattern( Config::$data[ "componentInstanceFilePattern" ], array( "component" => "*", "instance" => "*" ) );

		foreach( glob( $instances ) as $file )
		{
			$model = new XMLModelDriver( $file );

			foreach( $model->xPath->query( "//meta:href" ) as $hrefNode )
			{
				$name = $model->xPath->query( "ancestor::instance:*/@name", $hrefNode )->item( 0 )->nodeValue;
				$lang = $hrefNode->getAttribute( "xml:lang" );
				$parent = $model->xPath->query( "../meta:parent", $hrefNode )->item( 0 )->nodeValue;
				$component = $model->xPath->query( "ancestor::instance:*", $hrefNode )->item( 0 )->localName;
				$view = $model->xPath->query( "../meta:view", $hrefNode )->item( 0 )->nodeValue;

				$links[ $lang ][ $name ] = array(
					"path" => $hrefNode->nodeValue,
					"parent" => $parent,
					"file" => $file,
					"component" => $component,
					"view" => $view
				);
			}
		}

		return( $links );
	}

	private static function GenerateSitemapModelForLanguage( $lang, $linkData )
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
			$view = $data[ "view" ];

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

			$componentNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:component", $component );
			$urlNode->appendChild( $componentNode );

			$viewNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:view", $view );
			$urlNode->appendChild( $viewNode );
		}

		$sitemapModel->xPath->registerNamespace( "sitemap", Config::$data[ "ccNamespaces" ][ "sitemap" ] );

		return( $sitemapModel );
	}

	private static function WriteSitemapModelForLanguage( $lang, $sitemapModel )
	{
		$filename = self::NormalizeSitemapXMLFilePattern( $lang );

		if( Cache::PrepCacheFolder( $filename ) )
		{
			$sitemapXML = "<" . "?xml version=\"1.0\" encoding=\"utf-8\"?" . ">" . $sitemapModel->GetXMLForStacking();
			return( file_put_contents( $filename, $sitemapXML, FILE_TEXT ) );
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
		}
		else
		{
			self::$models = self::GenerateSitemapModels();
		}

		foreach( array_keys( self::$models ) as $key )
		{
			self::$models[ $key ]->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );
		}

		return( self::$models[ $lang ] );
	}

	public static function NormalizeSitemapXMLFilePattern( $lang )
	{
		$filename = StringUtils::ReplaceTokensInPattern( Config::$data[ "sitemapXMLFilePattern" ], array( "lang" => $lang ) );

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
				$linkData[ "view" ] = $sitemapModel->xPath->query( "sitemap:view", $urlNode )->item( 0 )->nodeValue;
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

	public static function ReplacePageNameTokensWithPath()
	{
		foreach( array( "routes", "priorityRoutes", "lowPriorityRoutes" ) as $routeGroup )
		{
			foreach( array_keys( Config::$data[ $routeGroup ] ) as $pattern )
			{
				preg_match_all( "/#([A-Za-z0-9-_]+)#/", $pattern, $matches );

				if( count( $matches[ 0 ] ) )
				{
					$updatedPattern = $pattern;

					foreach( $matches[ 0 ] as $key => $match )
					{
						$updatedPattern = str_replace( $match, addcslashes( self::GetPathByPageNameAndLanguage( $matches[ 1 ][ $key ], Language::GetLang() ), "/" ), $updatedPattern );
					}

					Config::$data[ $routeGroup ][ $updatedPattern ] = Config::$data[ $routeGroup ][ $pattern ];
					unset( Config::$data[ $routeGroup ][ $pattern ] );
				}
			}
		}
	}
}

?>