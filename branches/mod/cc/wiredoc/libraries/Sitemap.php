<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Routing;
use xMVC\Sys\Config;
use xMVC\Sys\OutputHeaders;
use xMVC\Sys\FileSystem;
use xMVC\Sys\NamespaceMap;

use xMVC\Mod\Language\Language;
use xMVC\Mod\Utils\StringUtils;

class Sitemap
{
	private static $models;

	public static function Generate()
	{
		self::GenerateSitemapModels();

		foreach( self::$models as $lang => $sitemapModel )
		{
			self::WriteSitemapModelForLanguage( $sitemapModel, $lang );
		}
	}

	private static function GenerateSitemapModels()
	{
		$metaDataCollectionByLang = self::GetMetaDataCollectionByLangFromModels();

		self::$models = array();

		foreach( $metaDataCollectionByLang as $lang => $metaDataCollection )
		{
			self::$models[ $lang ] = self::GenerateSitemapModelForLanguage( $lang, $metaDataCollection );
		}
	}

	private static function GetMetaDataCollectionByLangFromModels()
	{
		$list = FileSystem::GetDirListRecursive( Config::$data[ "sitemapCrawlFolder" ], Config::$data[ "sitemapCrawlFileRegExp" ], Config::$data[ "sitemapCrawlFolderRegExp" ], false );
		$flatList = FileSystem::FlattenDirListIntoFileList( $list[ Config::$data[ "sitemapCrawlFolder" ] ] );

		$metaDataCollectionByLang = array();

		foreach( $flatList as $file )
		{
			$model = new XMLModelDriver( $file );
			$metaDataCollectionByLang = self::GetMetaData( $model, $file, $metaDataCollectionByLang );
		}

		return( $metaDataCollectionByLang );
	}

	public static function GetMetaData( $model, $file = null, $metaDataCollectionByLang = array() )
	{
		foreach( $model->xPath->query( "//meta:href" ) as $hrefNode )
		{
			$lang = $hrefNode->getAttribute( "xml:lang" );
			$componentNodeList = $model->xPath->query( "ancestor::component:definition/@name", $hrefNode );
			$instanceNameNodeList = $model->xPath->query( "ancestor::component:definition/@instance-name", $hrefNode );
			$viewNodeList = $model->xPath->query( "ancestor::component:definition/@view", $hrefNode );
			$parentNodeList = $model->xPath->query( "ancestor::component:definition/@parent", $hrefNode );

			$component = $componentNodeList->length > 0 ? $componentNodeList->item( 0 )->nodeValue : "";
			$instanceName = $instanceNameNodeList->length > 0 ? $instanceNameNodeList->item( 0 )->nodeValue : "";
			$view = $viewNodeList->length > 0 ? $viewNodeList->item( 0 )->nodeValue : "";
			$parent = $parentNodeList->length > 0 ? $parentNodeList->item( 0 )->nodeValue : "";

			$metaDataCollectionByLang[ $lang ][ $instanceName ] = array(
				"path" => $hrefNode->nodeValue,
				"parent" => $parent,
				"file" => $file,
				"component" => $component,
				"view" => $view
			);
		}

		return( $metaDataCollectionByLang );
	}

	private static function GenerateSitemapModelForLanguage( $lang, $metaDataCollection )
	{
		$sitemapModel = new XMLModelDriver();

		$sitemapModel->xPath->registerNamespace( "sitemap", Config::$data[ "ccNamespaces" ][ "sitemap" ] );
		$sitemapModel->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );

		$urlsetNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "urlset" );
		$sitemapModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $urlsetNode );

		$urlsetNode->setAttributeNS( "http://www.w3.org/2000/xmlns/", "xmlns:sitemap", Config::$data[ "ccNamespaces" ][ "sitemap" ] );

		foreach( $metaDataCollection as $name => $metaData )
		{
			self::AppendLinkDataEntry( $sitemapModel, $name, $metaData );
		}

		return( $sitemapModel );
	}

	private static function AppendLinkDataEntry( $sitemapModel, $name, $metaData )
	{
		$path = $metaData[ "path" ];
		$parent = $metaData[ "parent" ];
		$file = $metaData[ "file" ];
		$component = $metaData[ "component" ];
		$view = $metaData[ "view" ];

		$urlsetNode = $sitemapModel->xPath->query( "//s:urlset" )->item( 0 );

		$urlNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "url" );
		$urlsetNode->appendChild( $urlNode );

		$loc = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . $path;
		$locNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "loc", $loc );
		$urlNode->appendChild( $locNode );

		$lastMod = date( "Y-m-d", is_null( $file ) ? time() : filemtime( $file ) );
		$lastModNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "lastmod", $lastMod );
		$urlNode->appendChild( $lastModNode );

		$nameNode = $sitemapModel->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:instance-name", $name );
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

	private static function WriteSitemapModelForLanguage( $sitemapModel, $lang )
	{
		$filename = self::NormalizeSitemapXMLFilePattern( $lang );

		if( Cache::PrepCacheFolder( $filename ) )
		{
			$sitemapXML = "<" . "?xml version=\"1.0\" encoding=\"utf-8\"?" . ">" . $sitemapModel->GetXMLForStacking();
			$bytesWritten = file_put_contents( $filename, $sitemapXML, FILE_TEXT );

			return( $bytesWritten );
		}

		return( false );
	}

	public static function Load( $lang )
	{
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
			self::GenerateSitemapModels();
		}

		foreach( array_keys( self::$models ) as $key )
		{
			self::$models[ $key ]->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );
		}

		if( !isset( self::$models[ $lang ] ) )
		{
			self::$models[ $lang ] = null;
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
		if( !isset( self::$models[ $lang ] ) || is_null( self::$models[ $lang ] ) )
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
				$pageName = $sitemapModel->xPath->query( "sitemap:instance-name", $urlNode )->item( 0 )->nodeValue;

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

			if( !is_null( $sitemapModel ) )
			{
				foreach( $sitemapModel->xPath->query( "//s:url[ sitemap:path = '" . $path . "' ]" ) as $urlNode )
				{
					$linkData = array();
					$linkData[ "name" ] = $sitemapModel->xPath->query( "sitemap:instance-name", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "path" ] = $sitemapModel->xPath->query( "sitemap:path", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "component" ] = $sitemapModel->xPath->query( "sitemap:component", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "view" ] = $sitemapModel->xPath->query( "sitemap:view", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "lang" ] = $lang;

					return( $linkData );
				}
			}
		}

		return( false );
	}

	public static function GetPathByPageNameAndLanguage( $namespacedInstanceName, $lang )
	{
		$sitemapModel = self::Get( $lang );

		$pathNodeList = $sitemapModel->xPath->query( "//s:url/sitemap:path[ concat( ../sitemap:component, '\\', ../sitemap:instance-name ) = '" . $namespacedInstanceName . "' ]" );
		$path = $pathNodeList->length > 0 ? $pathNodeList->item( 0 )->nodeValue : "";

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
				preg_match_all( "/#([A-Za-z0-9-_\\\\]+)#/", $pattern, $matches );

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

	public static function MetaDataAlreadyPresent( $metaDataCollectionByLang, $lang )
	{
		$sitemapModel = self::$models[ $lang ];

		if( !is_null( $sitemapModel ) )
		{
			$name = key( $metaDataCollectionByLang[ $lang ] );
			$metaData = current( $metaDataCollectionByLang[ $lang ] );

			return( $sitemapModel->xPath->query( "//s:url[ sitemap:instance-name = '" . $name . "' and sitemap:component = '" . $metaData[ "component" ]. "' ]" )->length > 0 );
		}

		return( false );
	}

	public static function AddMetaDataCollectionByLangToSitemap( $metaDataCollectionByLang )
	{
		foreach( array_keys( self::$models ) as $lang )
		{
			if( !is_null( self::$models[ $lang ] ) )
			{
				$name = key( $metaDataCollectionByLang[ $lang ] );
				$metaData = current( $metaDataCollectionByLang[ $lang ] );

				self::AppendLinkDataEntry( self::$models[ $lang ], $name, $metaData );
				self::WriteSitemapModelForLanguage( self::$models[ $lang ], $lang );
			}
		}
	}
}

?>