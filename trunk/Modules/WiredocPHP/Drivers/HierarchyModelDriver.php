<?php

namespace Modules\WiredocPHP\Drivers;

use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\Config;
use Modules\Language\Libraries\Language;
use Modules\WiredocPHP\Libraries\Components\ComponentLookup;
use Modules\WiredocPHP\Libraries\Components\ComponentUtils;

class HierarchyModelDriver extends ModelDriver implements IModelDriver
{
	private $lookupModel;

	public function __construct( $component, $instanceName, $fullyQualifiedName, $currentHref )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "wiredocNamespaces" ][ "sitemap" ], "sitemap:hierarchy" );
		$this->appendChild( $this->rootElement );

		$this->lookupModel = ComponentLookup::getInstance()->get();

		$this->transformForeignToXML( $component, $instanceName, $fullyQualifiedName, $currentHref );
	}

	public function transformForeignToXML()
	{
		$component = func_get_arg( 0 );
		$instanceName = func_get_arg( 1 );
		$fullyQualifiedName = func_get_arg( 2 );
		$currentHref = func_get_arg( 3 );

		$nodeList = $this->lookupModel->xPath->query( "//lookup:entry[ lookup:component = '" . $component . "' and ( lookup:instance-name = '" . $instanceName . "' or lookup:instance-name = 'null' ) and lookup:href[ lookup:uri = '" . $currentHref . "' ] ]" );

		if( $nodeList->length > 0 )
		{
			$node = $nodeList->item( 0 );
			$this->addHierarchyEntry( $node );
			$this->crawlSitemapFollowingParentsOf( $node );
		}

		parent::transformForeignToXML();
	}

	private function crawlSitemapFollowingParentsOf( $node )
	{
		$parentNodeList = $this->lookupModel->xPath->query( "lookup:parent", $node );

		if( $parentNodeList->length > 0 )
		{
			$fullyQualifiedParentName = $parentNodeList->item( 0 )->nodeValue;

			$parentNodeList = $this->lookupModel->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $fullyQualifiedParentName . "' ]" );

			if( $parentNodeList->length > 0 )
			{
				$parentNode = $parentNodeList->item( 0 );
				$this->addHierarchyEntry( $parentNode );
				$this->crawlSitemapFollowingParentsOf( $parentNode );
			}
		}

	}

	private function addHierarchyEntry( $node )
	{
		$URINodeList = $this->lookupModel->xPath->query( "lookup:href[ php:function( 'Modules\Language\Libraries\Language::XSLTLang', '" . Language::getLang() . "', (ancestor-or-self::*/@xml:lang)[last()] ) ]/lookup:uri", $node );

		if( $URINodeList->length > 0 )
		{
			$URI = $URINodeList->item( 0 )->nodeValue;

			$pathNode = $this->createElementNS( Config::$data[ "wiredocNamespaces" ][ "sitemap" ], "sitemap:path" );
			$data = $this->createCDATASection( ( string )$URI );
			$pathNode->appendChild( $data );
			$this->rootElement->appendChild( $pathNode );
		}
	}
}