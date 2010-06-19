<?php

namespace xMVC\Mod\WebWiredoc;

use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\Config;
use xMVC\Mod\Language\Language;
use xMVC\Mod\WebWiredoc\Components\ComponentLookup;
use xMVC\Mod\WebWiredoc\Components\ComponentUtils;

class HierarchyModelDriver extends ModelDriver implements IModelDriver
{
	private $lookupModel;

	public function __construct( $component, $instanceName )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "wiredocNamespaces" ][ "sitemap" ], "sitemap:hierarchy" );
		$this->appendChild( $this->rootElement );

		$this->lookupModel = ComponentLookup::getInstance()->Get();

		$this->TransformForeignToXML( $component, $instanceName );
	}

	public function TransformForeignToXML()
	{
		$component = func_get_arg( 0 );
		$instanceName = func_get_arg( 1 );

		$nodeList = $this->lookupModel->xPath->query( "//lookup:entry[ lookup:component = '" . $component . "' and ( lookup:instance-name = '" . $instanceName . "' or lookup:instance-name = 'null' ) ]" );

		if( $nodeList->length > 0 )
		{
			$node = $nodeList->item( 0 );
			$this->AddHierarchyEntry( $node );
			$this->CrawlSitemapFollowingParentsOf( $node );
		}

		parent::TransformForeignToXML();
	}

	private function CrawlSitemapFollowingParentsOf( $node )
	{
		$parentNodeList = $this->lookupModel->xPath->query( "lookup:parent", $node );

		if( $parentNodeList->length > 0 )
		{
			$fullyQualifiedParentName = $parentNodeList->item( 0 )->nodeValue;

			$parentNodeList = $this->lookupModel->xPath->query( "//lookup:entry[ lookup:fully-qualified-name = '" . $fullyQualifiedParentName . "' ]" );

			if( $parentNodeList->length > 0 )
			{
				$parentNode = $parentNodeList->item( 0 );
				$this->AddHierarchyEntry( $parentNode );
				$this->CrawlSitemapFollowingParentsOf( $parentNode );
			}
		}

	}

	private function AddHierarchyEntry( $node )
	{
		$URINodeList = $this->lookupModel->xPath->query( "lookup:href[ php:function( 'xMVC\Mod\Language\Language::XSLTLang', '" . Language::GetLang() . "', (ancestor-or-self::*/@xml:lang)[last()] ) ]/lookup:uri", $node );

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