<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\Normalize;
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
	private static $caches;

	public static function Generate()
	{
		self::GenerateSitemapModels();

		foreach( self::$models as $lang => $sitemapModel )
		{
			self::CacheSitemapModelForLanguage( $sitemapModel, $lang );
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
			$lang = $hrefNode->hasAttribute( "xml:lang" ) ? $hrefNode->getAttribute( "xml:lang" ) : Language::GetLang();
			$private = $hrefNode->hasAttribute( "private" ) ? $hrefNode->getAttribute( "private" ) : "0";
			$componentNodeList = $model->xPath->query( "ancestor::component:definition/@name", $hrefNode );
			$instanceNameNodeList = $model->xPath->query( "ancestor::component:definition/@instance-name", $hrefNode );
			$viewNodeList = $model->xPath->query( "ancestor::component:definition/@view", $hrefNode );
			$parentNodeList = $model->xPath->query( "ancestor::component:definition/@parent", $hrefNode );

			$component = $componentNodeList->length > 0 ? $componentNodeList->item( 0 )->nodeValue : "";
			$instanceName = $instanceNameNodeList->length > 0 ? $instanceNameNodeList->item( 0 )->nodeValue : "";
			$view = $viewNodeList->length > 0 ? $viewNodeList->item( 0 )->nodeValue : "";
			$parent = $parentNodeList->length > 0 ? $parentNodeList->item( 0 )->nodeValue : "";
			$private = ( $private == "true" || $private == 1 ? 1 : 0 );

			$fullyQualifiedName = self::BuildFullyQualifiedName( $component, $instanceName );

			$metaDataCollectionByLang[ $lang ][ $fullyQualifiedName ] = array(
				"path" => Normalize::URI( $hrefNode->nodeValue ),
				"parent" => $parent,
				"file" => $file,
				"component" => $component,
				"instanceName" => $instanceName,
				"fullyQualifiedName" => $fullyQualifiedName,
				"view" => $view,
				"private" => $private
			);
		}

		return( $metaDataCollectionByLang );
	}

	private static function GenerateSitemapModelForLanguage( $lang, $metaDataCollection )
	{
		$sitemapModel = new XMLModelDriver();

		$sitemapModel->xPath->registerNamespace( "sitemap", Config::$data[ "wirekitNamespaces" ][ "sitemap" ] );
		$sitemapModel->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );

		$urlsetNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "urlset" );
		$sitemapModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $urlsetNode );

		$urlsetNode->setAttributeNS( "http://www.w3.org/2000/xmlns/", "xmlns:sitemap", Config::$data[ "wirekitNamespaces" ][ "sitemap" ] );

		foreach( $metaDataCollection as $metaData )
		{
			self::AppendLinkDataEntry( $sitemapModel, $metaData );
		}

		return( $sitemapModel );
	}

	private static function AppendLinkDataEntry( $sitemapModel, $metaData )
	{
		$path = $metaData[ "path" ];
		$parent = $metaData[ "parent" ];
		$file = $metaData[ "file" ];
		$component = $metaData[ "component" ];
		$instanceName = $metaData[ "instanceName" ];
		$fullyQualifiedName = $metaData[ "fullyQualifiedName" ];
		$view = $metaData[ "view" ];
		$private = $metaData[ "private" ];

		$urlsetNode = $sitemapModel->xPath->query( "//s:urlset" )->item( 0 );

		$urlNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "url" );
		$urlsetNode->appendChild( $urlNode );

		$loc = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . $path;
		$locNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "loc", $loc );
		$urlNode->appendChild( $locNode );

		$lastMod = date( "Y-m-d", is_null( $file ) ? time() : filemtime( $file ) );
		$lastModNode = $sitemapModel->createElementNS( Config::$data[ "sitemapNamespace" ], "lastmod", $lastMod );
		$urlNode->appendChild( $lastModNode );

		$pathNode = $sitemapModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:path", $path );
		$urlNode->appendChild( $pathNode );

		$componentNode = $sitemapModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:component", $component );
		$urlNode->appendChild( $componentNode );

		$instanceNameNode = $sitemapModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:instance-name", $instanceName );
		$urlNode->appendChild( $instanceNameNode );

		$fullyQualifiedNameNode = $sitemapModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:fully-qualified-name", $fullyQualifiedName );
		$urlNode->appendChild( $fullyQualifiedNameNode );

		if( strlen( $parent ) > 0 )
		{
			$parentNode = $sitemapModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:parent", $parent );
			$urlNode->appendChild( $parentNode );
		}

		$viewNode = $sitemapModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:view", $view );
		$urlNode->appendChild( $viewNode );

		$privateNode = $sitemapModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:private", $private );
		$urlNode->appendChild( $privateNode );
	}

	private static function CacheSitemapModelForLanguage( $sitemapModel, $lang )
	{
		self::$caches[ $lang ] = new Cache( Config::$data[ "sitemapXMLFilePattern" ], array( "lang" => $lang ), "", false );

		return( self::$caches[ $lang ]->Write( $sitemapModel ) );
	}

	public static function Load( $lang )
	{
		if( !isset( self::$models[ $lang ] ) )
		{
			// Sitemap isn't available in local memory, check the cache
			if( !isset( self::$caches[ $lang ] ) || !self::$caches[ $lang ]->IsCached() )
			{
				// Nothing is cached, load into local memory and attempt to cache
				self::Generate();
			}

			if( isset( self::$caches[ $lang ] ) && self::$caches[ $lang ]->IsCached() )
			{
				// Available in cache, grab it from there and store in local memory
				self::$models[ $lang ] = self::$caches[ $lang ]->Read();
			}
			else
			{
				// Still unable to grab from cache, might be a write permission problem etc.
				// Ignore cache, load to local memory and continue
				self::GenerateSitemapModels();
			}
		}

		// Sitemap in local memory should finally be available
		if( isset( self::$models[ $lang ] ) )
		{
			foreach( array_keys( self::$models ) as $key )
			{
				self::$models[ $key ]->xPath->registerNamespace( "s", Config::$data[ "sitemapNamespace" ] );
			}
		}
		else
		{
			// Final fallback in case we still couldn't load sitemap despite all measures taken
			self::$models[ $lang ] = null;
		}

		return( self::$models[ $lang ] );
	}

	private static function BuildFullyQualifiedName( $component, $instanceName )
	{
		$pageNameList = array();

		if( $component != "" )
		{
			$pageNameList[] = $component;
		}

		if( $instanceName != "" )
		{
			$pageNameList[] = $instanceName;
		}

		return( implode( "\\", $pageNameList ) );
	}

	public static function GetSitemapXMLFilePattern( $lang )
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

	public static function GetCurrentFullyQualifiedPageName()
	{
		$pathOnlyOriginal = Routing::GetPathOnlyOriginal();
		$currentPath = "/" . ( strlen( $pathOnlyOriginal ) > 0 ? $pathOnlyOriginal . "/" : "" );

		return( self::GetFullyQualifiedNameByPath( $currentPath ) );
	}

	public static function GetFullyQualifiedNameByPath( $path )
	{
		foreach( Language::GetDefinedLangs() as $lang )
		{
			$sitemapModel = self::Get( $lang );

			foreach( $sitemapModel->xPath->query( "//s:url[ sitemap:path = '" . $path . "' ]" ) as $urlNode )
			{
				$fullyQualifiedNameNodeList = $sitemapModel->xPath->query( "sitemap:fully-qualified-name", $urlNode );
				$fullyQualifiedName = $fullyQualifiedNameNodeList->length > 0 ? $fullyQualifiedNameNodeList->item( 0 )->nodeValue : "";

				return( $fullyQualifiedName );
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
					$linkData[ "path" ] = $sitemapModel->xPath->query( "sitemap:path", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "instanceName" ] = $sitemapModel->xPath->query( "sitemap:instance-name", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "component" ] = $sitemapModel->xPath->query( "sitemap:component", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "fullyQualifiedName" ] = $sitemapModel->xPath->query( "sitemap:fully-qualified-name", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "view" ] = $sitemapModel->xPath->query( "sitemap:view", $urlNode )->item( 0 )->nodeValue;
					$linkData[ "lang" ] = $lang;

					return( $linkData );
				}
			}
		}

		return( false );
	}

	public static function GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $lang )
	{
		$sitemapModel = self::Get( $lang );

		$pathNodeList = $sitemapModel->xPath->query( "//s:url/sitemap:path[ ../sitemap:fully-qualified-name = '" . $fullyQualifiedName . "' ]" );
		$path = $pathNodeList->length > 0 ? $pathNodeList->item( 0 )->nodeValue : "";

		return( $path );
	}

	public static function GetSitemapXMLFilenames()
	{
		$filenames = array();

		foreach( glob( self::GetSitemapXMLFilePattern( "*" ) ) as $filename )
		{
			$filenames[] = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/" . basename( $filename );
		}

		return( $filenames );
	}

	public static function Output( $lang )
	{
		OutputHeaders::XML();

		$sitemapModel = self::Get( $lang );

		foreach( $sitemapModel->xPath->query( "//s:url[ sitemap:private = '1' ]" ) as $node )
		{
			$node->parentNode->removeChild( $node );
		}

		echo( Normalize::StripRootTag( $sitemapModel->saveXML() ) );
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
						$updatedPattern = str_replace( $match, addcslashes( self::GetPathByFullyQualifiedNameAndLanguage( $matches[ 1 ][ $key ], Language::GetLang() ), "/" ), $updatedPattern );
					}

					Config::$data[ $routeGroup ][ $updatedPattern ] = Config::$data[ $routeGroup ][ $pattern ];
					unset( Config::$data[ $routeGroup ][ $pattern ] );
				}
			}
		}
	}

	public static function EnsureInstanceInSitemap( $model )
	{
		if( $model->xPath->query( "//meta:href" )->length > 0 )
		{
			$metaDataCollectionByLang = self::GetMetaData( $model );

			if( !self::MetaDataAlreadyPresent( $metaDataCollectionByLang, Language::GetLang() ) )
			{
				self::AddMetaDataCollectionByLangToSitemap( $metaDataCollectionByLang );
			}
		}
	}

	public static function MetaDataAlreadyPresent( $metaDataCollectionByLang, $lang )
	{
		$sitemapModel = self::$models[ $lang ];

		if( !is_null( $sitemapModel ) )
		{
			$metaData = current( $metaDataCollectionByLang[ $lang ] );

			return( $sitemapModel->xPath->query( "//s:url[ sitemap:fully-qualified-name = '" . $metaData[ "fullyQualifiedName" ] . "' ]" )->length > 0 );
		}

		return( false );
	}

	public static function AddMetaDataCollectionByLangToSitemap( $metaDataCollectionByLang )
	{
		foreach( array_keys( self::$models ) as $lang )
		{
			if( !is_null( self::$models[ $lang ] ) )
			{
				$metaData = current( $metaDataCollectionByLang[ $lang ] );

				self::AppendLinkDataEntry( self::$models[ $lang ], $metaData );
				self::CacheSitemapModelForLanguage( self::$models[ $lang ], $lang );
			}
		}
	}
}

?>