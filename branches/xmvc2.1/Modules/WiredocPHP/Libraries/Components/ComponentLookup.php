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

	public function generate()
	{
		$this->generateLookupModel();
		$this->cacheLookupModel();
	}

	private function generateLookupModel()
	{
		$this->model = $this->generateComponentModel( $this->getMetaDataCollectionFromModels() );
	}

	private function getMetaDataCollectionFromModels()
	{
		$list = FileSystem::getDirListRecursive( Config::$data[ "componentLookupCrawlFolder" ], Config::$data[ "componentLookupCrawlFileRegExp" ], Config::$data[ "componentLookupCrawlFolderRegExp" ], false );
		$flatList = FileSystem::flattenDirListIntoFileList( $list[ Config::$data[ "componentLookupCrawlFolder" ] ] );

		$metaDataCollection = array();

		foreach( $flatList as $file )
		{
			$model = new XMLModelDriver( $file );
			ComponentUtils::registerNamespaces( $model );
			$metaDataCollection = $this->getMetaData( $model, $file, $metaDataCollection );
		}

		return $metaDataCollection;
	}

	public function getMetaData( $model, $file = null, $metaDataCollection = array() )
	{
		$componentNodeList = $model->xPath->query( "//wd:component" );

		foreach( $componentNodeList as $componentNode )
		{
			$hrefList = array();
			$hrefNodeList = $model->xPath->query( "//meta:href", $componentNode );

			foreach( $hrefNodeList as $hrefNode )
			{
				$lang = $hrefNode->hasAttribute( "xml:lang" ) ? $hrefNode->getAttribute( "xml:lang" ) : Language::getLang();
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
				list( $component, $instanceName, $fullyQualifiedName ) = ComponentUtils::extractComponentNamePartsFromWiredocName( $componentNode->getAttribute( "wd:name" ) );
			}
			else
			{
				list( $component, $instanceName, $fullyQualifiedName ) = $this->extractComponentNamingFromFile( $file );
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

	private function generateComponentModel( $metaDataCollection )
	{
		$lookupModel = new XMLModelDriver();
		$lookupModel->xPath->registerNamespace( "lookup", Config::$data[ "wiredocNamespaces" ][ "lookup" ] );

		$componentsNode = $lookupModel->createElementNS( Config::$data[ "wiredocNamespaces" ][ "lookup" ], "lookup:components" );
		$lookupModel->xPath->query( "/xmvc:root" )->item( 0 )->appendChild( $componentsNode );

		$componentsNode->setAttributeNS( "http://www.w3.org/2000/xmlns/", "xmlns:lookup", Config::$data[ "wiredocNamespaces" ][ "lookup" ] );

		foreach( $metaDataCollection as $metaData )
		{
			$this->appendEntry( $metaData, $lookupModel );
		}

		return $lookupModel;
	}

	private function appendEntry( $metaData, $lookupModel )
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

	private function cacheLookupModel()
	{
		return $this->cache->write( $this->model );
	}

	private function load()
	{
		if( is_null( $this->model ) )
		{
			// Sitemap isn't available in local memory, check the cache
			if( ! $this->cache->isCached() )
			{
				// Nothing is cached, load into local memory and attempt to cache
				$this->generate();
			}

			if( $this->cache->isCached() )
			{
				// Available in cache, grab it from there and store in local memory
				$this->model = $this->cache->read();
			}
			else
			{
				// Still unable to grab from cache, might be a write permission problem etc.
				// Ignore cache, load to local memory and continue
				$this->generateSitemapModels();
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

	private function extractComponentNamingFromFile( $file )
	{
		$componentString = str_replace( "\\", "/", str_replace( Normalize::path( realpath( Config::$data[ "componentLookupCrawlFolder" ] ) ), "", $file ) );

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

		return ComponentUtils::extractComponentNamePartsFromWiredocName( $componentWiredocName );
	}

	public function get()
	{
		if( is_null( $this->model ) )
		{
			$this->load();
		}

		return $this->model;
	}

	public function refresh()
	{
		$this->model = null;
		$this->load();
	}

	public function hostsDontMatch( $host = null )
	{
		if( is_null( $host ) )
		{
			$host = $_SERVER[ "HTTP_HOST" ];
		}

		$lookupModel = $this->get();

		$URINodeList = $lookupModel->xPath->query( "//lookup:entry/lookup:href/lookup:fully-qualified-uri" );

		if( $URINodeList->length > 0 )
		{
			$exampleURI = $URINodeList->item( 0 )->nodeValue;
			return strpos( $exampleURI, $host ) === false;
		}

		return false;
	}

	public function getComponentDataByPath( $path, $index = 0 )
	{
		$lookupModel = $this->get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:instance-name != '' and lookup:instance-name != 'null' and lookup:href/lookup:uri = '" . $path . "' ]" );

		if( $entryNodeList->length > 0 )
		{
			return $this->getComponentData( $entryNodeList->item( $index ), $path );
		}

		return false;
	}

	public function getComponentDataByFullyQualifiedName( $fullyQualifiedName, $index = 0 )
	{
		$lookupModel = $this->get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $fullyQualifiedName . "' ]" );

		if( $entryNodeList->length > 0 )
		{
			return $this->getComponentData( $entryNodeList->item( $index ) );
		}

		return false;
	}

	public function getPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $lang, $index = 0 )
	{
		$lookupModel = $this->get();
		$URINodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $fullyQualifiedName . "' ]/lookup:href[ php:function( 'Modules\Language\Libraries\Language::XSLTLang', '" . $lang . "', (ancestor-or-self::*/@xml:lang)[last()] ) ]/lookup:uri" );
		$path = $URINodeList->length > 0 ? $URINodeList->item( $index )->nodeValue : "";

		return $path;
	}

	public function getComponentDataByComponentName( $component, $index = 0 )
	{
		$lookupModel = $this->get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry[ lookup:component = '" . $component . "' ]" );

		if( $entryNodeList->length > 0 )
		{
			return $this->getComponentData( $entryNodeList->item( $index ) );
		}

		return false;
	}

	private function getComponentData( $entryNode, $path = "" )
	{
		$lookupModel = $this->get();

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

	public function getFullyQualifiedNameByPath( $path )
	{
		$lookupModel = $this->get();
		$entryNodeList = $lookupModel->xPath->query( "//lookup:entry/lookup:href[ lookup:uri = '" . $path . "' ]" );

		foreach( $entryNodeList as $entryNode )
		{
			$fullyQualifiedNameNodeList = $lookupModel->xPath->query( "../lookup:fully-qualified-name", $entryNode );
			$fullyQualifiedName = $fullyQualifiedNameNodeList->length > 0 ? $fullyQualifiedNameNodeList->item( 0 )->nodeValue : "";

			return $fullyQualifiedName;
		}

		return false;
	}

	public function ensureInstanceInLookup( $model )
	{
		if( $model->xPath->query( "//meta:href" )->length > 0 )
		{
			$metaDataCollection = $this->getMetaData( $model );

			if( !$this->metaDataAlreadyPresent( $metaDataCollection ) )
			{
				$this->addMetaDataCollectionToComponentLookup( $metaDataCollection );
			}
		}
	}

	public function metaDataAlreadyPresent( $metaDataCollection )
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

	public function addMetaDataCollectionToComponentLookup( $metaDataCollection )
	{
		$metaData = current( $metaDataCollection );

		$this->appendEntry( $metaData, $this->get() );
		$this->cacheLookupModel();
	}
}