<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\IModelDriver;
use xMVC\Sys\Config;
use xMVC\Mod\Language\Language;
use xMVC\Mod\WireKit\Components\ComponentLookup;
use xMVC\Mod\WireKit\Components\ComponentUtils;

class HierarchyModelDriver extends ModelDriver implements IModelDriver
{
	private $lookupModel;

	public function __construct( $component, $instanceName )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:hierarchy" );
		$this->appendChild( $this->rootElement );

		$this->lookupModel = ComponentLookup::getInstance()->Get();

		$this->TransformForeignToXML( $component, $instanceName );
	}

	public function TransformForeignToXML()
	{
		$component = func_get_arg( 0 );
		$instanceName = func_get_arg( 1 );

		$nodeList = $this->lookupModel->xPath->query( "//lookup:entry[ lookup:component = '" . $component . "' and lookup:instance-name = '" . $instanceName . "' ]" );

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
			$fullyQualifiedParentName = ComponentUtils::GetFullyQualifiedComponent( $parentNodeList->item( 0 )->nodeValue );

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
		$uri = $this->lookupModel->xPath->query( "lookup:href[ lang( '" . Language::GetLang() . "' ) ]/lookup:uri", $node )->item( 0 )->nodeValue;

		$pathNode = $this->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:path" );
		$data = $this->createCDATASection( ( string )$uri );
		$pathNode->appendChild( $data );
		$this->rootElement->appendChild( $pathNode );
	}
}

?>