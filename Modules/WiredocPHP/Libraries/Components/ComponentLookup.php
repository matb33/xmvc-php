<?php

namespace Modules\WiredocPHP\Libraries\Components;

use System\Libraries\Config;
use System\Drivers\XMLModelDriver;
use System\Libraries\FileSystem;
use System\Libraries\Normalize;
use System\Libraries\Routing;
use System\Libraries\OverrideableSingleton;
use Modules\Language\Libraries\Language;
use Modules\Cache\Libraries\Cache;

class ComponentLookup extends OverrideableSingleton
{
	private $model = null;
	private $cache = null;

	protected function __construct()
	{
		$this->cache = new Cache( Config::$data[ "componentLookupFilePattern" ], array(), "", false, 0 );
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
			ComponentUtils::RegisterNamespaces( $model );
			$metaDataCollection = $this->GetMetaData( $model, $file, $metaDataCollection );
		}

		return $metaDataCollection;
	}

	public function GetMetaData( $model, $file = null, $metaDataCollection = array() )
	{
		$componentNodeList = $model->xPath->query( "//wd:component" );

		foreach( $componentNodeList as $componentNode )
		{
			$hrefList = array();
			$hrefNodeList = $model->xPath->query( "//meta:href", $componentNode );

			foreach( $hrefNodeList as $hrefNode )
			{
				$lang = $hrefNode->hasAttribute( "xml:lang" ) ? $hrefNode->getAttribute( "xml:lang" ) : Language::GetLang();
				$private = $hrefNode->hasAttribute( "private" ) ? $hrefNode->getAttribute( "private" ) : "0";
				$private = ( $private == "true" || $private == 1 ? 1 : 0 );
				$path = Normalize::URI( $hrefNode->nodeValue );

				$hrefList[ $lang ] = array( "path" => $path, "private" => $private, "lang" => $lang );
			}

			$parentNodeList = $model->xPath->query( "//meta:parent", $componentNode );
			$parent = $parentNodeList->length > 0 ? $parentNodeList->item( 0 )->nodeValue : "";

			//$viewNodeList = $model->xPath->query( "//meta:view", $componentNode );
			$view = ""; //TODO: finish removing view from lookup $viewNodeList->length > 0 ? $viewNodeList->item( 0 )->nodeValue : "";

			if( is_null( $file ) )
			{
				list( $component, $instanceName, $fullyQualifiedName ) = ComponentUtils::ExtractComponentNamePartsFromWiredocName( $componentNode->getAttribute( "wd:name" ) );
			}
			else
			{
				list( $component, $instanceName, $fullyQualifiedName ) = $this->ExtractComponentNamingFromFile( $file );
			}

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

		return $metaDataCollection;
	}

	private function GenerateComponentModel( $metaDataCollection )
	{
		$lookupModel = new XMLModelDriver();
		$lookupModel->xPath->registerNamespace( "lookup", Config::$data[ "wiredocNamespaces" ][ "lookup" ] );

		$componentsNode = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:components" );
		$lookupModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $componentsNode );

		$componentsNode->setAttributeNS( "http://www.w3.org/2000/xmlns/", "xmlns:lookup", Config::$data[ "wiredocNamespaces" ][ "lookup" ] );

		foreach( $metaDataCollection as $metaData )
		{
			$this->AppendEntry( $metaData, $lookupModel );
		}

		return $lookupModel;
	}

	private function AppendEntry( $metaData, $lookupModel )
	{
		$hrefList = $metaData[ "hrefList" ];
		$component = $metaData[ "component" ];
		$instanceName = $metaData[ "instanceName" ];
		$view = $metaData[ "view" ];
		$parent = $metaData[ "parent" ];
		$fullyQualifiedName = $metaData[ "fullyQualifiedName" ];
		$file = $metaData[ "file" ];

		$componentsNode = $lookupModel->xPath->query( "//lookup:components" )->item( 0 );

		$entryNode = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:entry" );
		$componentsNode->appendChild( $entryNode );

		$created = date( "Y-m-d H:i:s", is_null( $file ) ? time() : filectime( $file ) );
		$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:created", $created );
		$entryNode->appendChild( $node );

		$modified = date( "Y-m-d H:i:s", is_null( $file ) ? time() : filemtime( $file ) );
		$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:modified", $modified );
		$entryNode->appendChild( $node );

		$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:component", $component );
		$entryNode->appendChild( $node );

		if( strlen( $instanceName ) > 0 )
		{
			$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:instance-name", $instanceName );
			$entryNode->appendChild( $node );
		}

		$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:fully-qualified-name", $fullyQualifiedName );
		$entryNode->appendChild( $node );

		if( strlen( $parent ) > 0 )
		{
			$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:parent", $parent );
			$entryNode->appendChild( $node );
		}

		if( strlen( $view ) > 0 )
		{
			$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:view", $view );
			$entryNode->appendChild( $node );
		}

		$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:file", $file );
		$entryNode->appendChild( $node );

		if( is_array( $hrefList ) )
		{
			foreach( $hrefList as $hrefInfo )
			{
				$hrefNode = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:href" );
				$entryNode->appendChild( $hrefNode );

				$attribute = $lookupModel->createAttributeNS( "http://www.w3.org/XML/1998/namespace", "xml:lang" );
				$attribute->nodeValue = $hrefInfo[ "lang" ];
				$hrefNode->appendChild( $attribute );

				$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:lang", $hrefInfo[ "lang" ] );
				$hrefNode->appendChild( $node );

				$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:uri", $hrefInfo[ "path" ] );
				$hrefNode->appendChild( $node );

				$loc = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . $hrefInfo[ "path" ];
				$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:fully-qualified-uri", $loc );
				$hrefNode->appendChild( $node );

				$node = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:private", $hrefInfo[ "private" ] );
				$hrefNode->appendChild( $node );
			}
		}
	}

	private function CacheLookupModel()
	{
		return $this->cache->Write( $this->model );
	}

	private function Load()
	{
		if( is_null( $this->model ) )
		{
			// Sitemap isn't available in local memory, check the cache
			if( ! $this->cache->IsCached() )
			{
				// Nothing is cached, load into local memory and attempt to cache
				$this->Generate();
			}

			if( $this->cache->IsCached() )
			{
				// Available in cache, grab it from there and store in local memory
				$this->model = $this->cache->Read();
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
			$this->model->xPath->registerNamespace( "lookup", Config::$data[ "wiredocNamespaces" ][ "lookup" ] );
		}
		else
		{
			// Final fallback in case we still couldn't load sitemap despite all measures taken
			$this->model = null;
		}
	}

	private function ExtractComponentNamingFromFile( $file )
	{
		$componentString = str_replace( "\\", "/", str_replace( Normalize::Path( realpath( Config::$data[ "componentLookupCrawlFolder" ] ) ), "", $file ) );

		if( strpos( $componentString, ".xsl" ) !== false )
		{
			$componentWiredocName = str_replace( ".xsl", "", $componentString );
			$componentWiredocName = substr( $componentWiredocName, 0, strrpos( $componentWiredocName, "/" ) ) . ".null";
		}
		else
		{
			$componentWiredocName = str_replace( ".xml", "", $componentString );
			$componentWiredocName = substr( $componentWiredocName, 0, strrpos( $componentWiredocName, "/" ) ) . "." . substr( strrchr( $componentWiredocName, "/" ), 1 );
		}

		return ComponentUtils::ExtractComponentNamePartsFromWiredocName( $componentWiredocName );
	}

	public function Get()
	{
		if( is_null( $this->model ) )
		{
			$this->Load();
		}

		return $this->model;
	}

	public function Refresh()
	{
		$this->model = null;
		$this->Load();
	}

	public function HostsDontMatch( $host = null )
	{
		if( is_null( $host ) )
		{
			$host = $_SERVER[ "HTTP_HOST" ];
		}

		$lookupModel = $this->Get();

		$URINodeList = $lookupModel->xPath->query( "//lookup:entry/lookup:href/lookup:uri" );

		if( $URINodeList->length > 0 )
		{
			$exampleURI = $URINodeList->item( 0 )->nodeValue;
			return strpos( $exampleURI, $host ) === false;
		}

		return false;
	}

	public function GetComponentDataByPath( $path, $index = 0 )
	{
		$lookupModel = $this->Get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:instance-name != '' and lookup:instance-name != 'null' and lookup:href/lookup:uri = '" . $path . "' ]" );

		if( $entryNodeList->length > 0 )
		{
			return $this->GetComponentData( $entryNodeList->item( $index ), $path );
		}

		return false;
	}

	public function GetComponentDataByFullyQualifiedName( $fullyQualifiedName, $index = 0 )
	{
		$lookupModel = $this->Get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $fullyQualifiedName . "' ]" );

		if( $entryNodeList->length > 0 )
		{
			return $this->GetComponentData( $entryNodeList->item( $index ) );
		}

		return false;
	}

	public function GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $lang, $index = 0 )
	{
		$lookupModel = $this->Get();
		$URINodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $fullyQualifiedName . "' ]/lookup:href[ php:function( 'Modules\Language\Libraries\Language::XSLTLang', '" . $lang . "', (ancestor-or-self::*/@xml:lang)[last()] ) ]/lookup:uri" );
		$path = $URINodeList->length > 0 ? $URINodeList->item( $index )->nodeValue : "";

		return $path;
	}

	public function GetComponentDataByComponentName( $component, $index = 0 )
	{
		$lookupModel = $this->Get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:component = '" . $component . "' ]" );

		if( $entryNodeList->length > 0 )
		{
			return $this->GetComponentData( $entryNodeList->item( $index ) );
		}

		return false;
	}

	private function GetComponentData( $entryNode, $path = "" )
	{
		$lookupModel = $this->Get();

		$componentNodeList = $lookupModel->xPath->query( "lookup:component", $entryNode );
		$instanceNameNodeList = $lookupModel->xPath->query( "lookup:instance-name", $entryNode );
		$FQNNodeList = $lookupModel->xPath->query( "lookup:fully-qualified-name", $entryNode );
		$matchingLang = $lookupModel->xPath->query( "lookup:href[ lookup:uri = '" . $path . "' ]/lookup:lang", $entryNode );

		$data = array();
		$data[ "component" ] = $componentNodeList->length > 0 ? $componentNodeList->item( 0 )->nodeValue : "";
		$data[ "instanceName" ] = $instanceNameNodeList->length > 0 ? $instanceNameNodeList->item( 0 )->nodeValue : "";
		$data[ "fullyQualifiedName" ] = $FQNNodeList->length > 0 ? $FQNNodeList->item( 0 )->nodeValue : "";
		$data[ "matchingLang" ] = $matchingLang->length == 1 ? $matchingLang->item( 0 )->nodeValue : "";

		return $data;
	}

	public function GetFullyQualifiedNameByPath( $path )
	{
		$lookupModel = $this->Get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:uri = '" . $path . "' ]" );

		foreach( $entryNodeList as $entryNode )
		{
			$fullyQualifiedNameNodeList = $lookupModel->xPath->query( "../lookup:fully-qualified-name", $entryNode );
			$fullyQualifiedName = $fullyQualifiedNameNodeList->length > 0 ? $fullyQualifiedNameNodeList->item( 0 )->nodeValue : "";

			return $fullyQualifiedName;
		}

		return false;
	}

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

			if( isset( $metaData[ "hrefList" ] ) && is_array( $metaData[ "hrefList" ] ) )
			{
				$pathSet = array();
				$hrefCriteria = "";

				foreach( $metaData[ "hrefList" ] as $lang => $info )
				{
					$pathSet[] = "lookup:uri = '" . $info[ "path" ] . "'";
				}

				if( count( $pathSet ) )
				{
					$hrefCriteria = " and lookup:href[ " . implode( " or ", $pathSet ) . " ]";
				}

				return $this->model->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $metaData[ "fullyQualifiedName" ] . "'" . $hrefCriteria . " ]" )->length > 0;
			}
		}

		return false;
	}

	public function AddMetaDataCollectionToComponentLookup( $metaDataCollection )
	{
		$metaData = current( $metaDataCollection );

		$this->AppendEntry( $metaData, $this->Get() );
		$this->CacheLookupModel();
	}
}