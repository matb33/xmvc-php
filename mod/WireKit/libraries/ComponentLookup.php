<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\Config;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\FileSystem;
use xMVC\Sys\Normalize;
use xMVC\Sys\Routing;
use xMVC\Mod\Language\Language;

class ComponentLookup extends Cache
{
	private $model = null;

	public function __construct()
	{
		parent::__construct( Config::$data[ "componentLookupFilePattern" ], array(), "", false, 0 );
	}

	public function Generate()
	{
		$this->GenerateLookupModel();
		$this->CacheLookupModel();
	}

	private function GenerateLookupModel()
	{
		$this->model = $this->GenerateComponentModel( $this->GetMetaDataCollectionFromModels() );
	}

	private function GetMetaDataCollectionFromModels()
	{
		$list = FileSystem::GetDirListRecursive( Config::$data[ "componentLookupCrawlFolder" ], Config::$data[ "componentLookupCrawlFileRegExp" ], Config::$data[ "componentLookupCrawlFolderRegExp" ], false );
		$flatList = FileSystem::FlattenDirListIntoFileList( $list[ Config::$data[ "componentLookupCrawlFolder" ] ] );

		$metaDataCollection = array();

		foreach( $flatList as $file )
		{
			$model = new XMLModelDriver( $file );
			$metaDataCollection = $this->GetMetaData( $model, $file, $metaDataCollection );
		}

		return( $metaDataCollection );
	}

	public function GetMetaData( $model, $file = null, $metaDataCollection = array() )
	{
		foreach( $model->xPath->query( "//component:definition" ) as $componentNode )
		{
			$hrefList = array();

			foreach( $model->xPath->query( "//meta:href", $componentNode ) as $hrefNode )
			{
				$lang = $hrefNode->hasAttribute( "xml:lang" ) ? $hrefNode->getAttribute( "xml:lang" ) : Language::GetLang();
				$private = $hrefNode->hasAttribute( "private" ) ? $hrefNode->getAttribute( "private" ) : "0";
				$private = ( $private == "true" || $private == 1 ? 1 : 0 );
				$path = Normalize::URI( $hrefNode->nodeValue );

				$hrefList[ $lang ] = array( "path" => $path, "private" => $private, "lang" => $lang );
			}

			$component = $componentNode->hasAttribute( "name" ) ? $componentNode->getAttribute( "name" ) : "";
			$instanceName = $componentNode->hasAttribute( "instance-name" ) ? $componentNode->getAttribute( "instance-name" ) : "";
			$view = $componentNode->hasAttribute( "view" ) ? $componentNode->getAttribute( "view" ) : "";
			$parent = $componentNode->hasAttribute( "parent" ) ? $componentNode->getAttribute( "parent" ) : "";
			$fullyQualifiedName = $this->BuildFullyQualifiedName( $component, $instanceName );

			$metaDataCollection[] = array(
				"hrefList" => $hrefList,
				"component" => $component,
				"instanceName" => $instanceName,
				"view" => $view,
				"parent" => $parent,
				"fullyQualifiedName" => $fullyQualifiedName,
				"file" => $file
			);
		}

		return( $metaDataCollection );
	}

	private function GenerateComponentModel( $metaDataCollection )
	{
		$lookupModel = new XMLModelDriver();
		$lookupModel->xPath->registerNamespace( "lookup", Config::$data[ "wirekitNamespaces" ][ "lookup" ] );

		$componentsNode = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:components" );
		$lookupModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $componentsNode );

		$componentsNode->setAttributeNS( "http://www.w3.org/2000/xmlns/", "xmlns:lookup", Config::$data[ "wirekitNamespaces" ][ "lookup" ] );

		foreach( $metaDataCollection as $metaData )
		{
			$this->AppendEntry( $metaData, $lookupModel );
		}

		return( $lookupModel );
	}

	private function AppendEntry( $metaData, &$lookupModel )
	{
		$hrefList = $metaData[ "hrefList" ];
		$component = $metaData[ "component" ];
		$instanceName = $metaData[ "instanceName" ];
		$view = $metaData[ "view" ];
		$parent = $metaData[ "parent" ];
		$fullyQualifiedName = $metaData[ "fullyQualifiedName" ];
		$file = $metaData[ "file" ];

		$componentsNode = $lookupModel->xPath->query( "//lookup:components" )->item( 0 );

		$entryNode = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:entry" );
		$componentsNode->appendChild( $entryNode );

		$created = date( "Y-m-d H:i:s", is_null( $file ) ? time() : filectime( $file ) );
		$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:created", $created );
		$entryNode->appendChild( $node );

		$modified = date( "Y-m-d H:i:s", is_null( $file ) ? time() : filemtime( $file ) );
		$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:modified", $modified );
		$entryNode->appendChild( $node );

		$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:component", $component );
		$entryNode->appendChild( $node );

		if( strlen( $instanceName ) > 0 )
		{
			$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:instance-name", $instanceName );
			$entryNode->appendChild( $node );
		}

		$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:fully-qualified-name", $fullyQualifiedName );
		$entryNode->appendChild( $node );

		if( strlen( $parent ) > 0 )
		{
			$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:parent", $parent );
			$entryNode->appendChild( $node );
		}

		if( strlen( $view ) > 0 )
		{
			$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:view", $view );
			$entryNode->appendChild( $node );
		}

		$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:file", $file );
		$entryNode->appendChild( $node );

		if( is_array( $hrefList ) )
		{
			foreach( $hrefList as $hrefInfo )
			{
				$hrefNode = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:href" );
				$entryNode->appendChild( $hrefNode );

				$attribute = $lookupModel->createAttributeNS( "http://www.w3.org/XML/1998/namespace", "xml:lang" );
				$attribute->nodeValue = $hrefInfo[ "lang" ];
				$hrefNode->appendChild( $attribute );

				$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:lang", $hrefInfo[ "lang" ] );
				$hrefNode->appendChild( $node );

				$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:uri", $hrefInfo[ "path" ] );
				$hrefNode->appendChild( $node );

				$loc = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . $hrefInfo[ "path" ];
				$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:fully-qualified-uri", $loc );
				$hrefNode->appendChild( $node );

				$node = $lookupModel->createElementNS( Config::$data[ "wirekitNamespaces" ][ "lookup" ], "lookup:private", $hrefInfo[ "private" ] );
				$hrefNode->appendChild( $node );
			}
		}
	}

	private function CacheLookupModel()
	{
		return( $this->Write( $this->model ) );
	}

	public function Load()
	{
		if( is_null( $this->model ) )
		{
			// Sitemap isn't available in local memory, check the cache
			if( ! $this->IsCached() )
			{
				// Nothing is cached, load into local memory and attempt to cache
				$this->Generate();
			}

			if( $this->IsCached() )
			{
				// Available in cache, grab it from there and store in local memory
				$this->model = $this->Read();
			}
			else
			{
				// Still unable to grab from cache, might be a write permission problem etc.
				// Ignore cache, load to local memory and continue
				$this->GenerateSitemapModels();
			}
		}

		// Sitemap in local memory should finally be available
		if( ! is_null( $this->model ) )
		{
			$this->model->xPath->registerNamespace( "lookup", Config::$data[ "wirekitNamespaces" ][ "lookup" ] );
		}
		else
		{
			// Final fallback in case we still couldn't load sitemap despite all measures taken
			$this->model = null;
		}

		return( $this->model );
	}

	private function BuildFullyQualifiedName( $component, $instanceName )
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

	public function Get()
	{
		if( is_null( $this->model ) )
		{
			$this->Load();
		}

		return( $this->model );
	}

	public function Refresh()
	{
		$this->model = null;
		$this->Load();
	}



	// Temporary approach until components are re-factored
	public function EnsureInstanceInLookup( $model )
	{
		if( $model->xPath->query( "//meta:href" )->length > 0 )
		{
			$metaDataCollection = $this->GetMetaData( $model );

			if( !$this->MetaDataAlreadyPresent( $metaDataCollection ) )
			{
				$this->AddMetaDataCollectionToComponentLookup( $metaDataCollection );
			}
		}
	}

	public function MetaDataAlreadyPresent( $metaDataCollection )
	{
		if( !is_null( $this->model ) )
		{
			$metaData = current( $metaDataCollection );

			return( $this->model->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $metaData[ "fullyQualifiedName" ] . "' ]" )->length > 0 );
		}

		return( false );
	}

	public function AddMetaDataCollectionToComponentLookup( $metaDataCollection )
	{
		$metaData = current( $metaDataCollection );

		$this->AppendEntry( $metaData, $this->Get() );
		$this->CacheLookupModel();
	}
}

?>