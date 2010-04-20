<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\ModelDriverInterface;
use xMVC\Sys\Config;
use xMVC\Mod\Language\Language;

class HierarchyModelDriver extends ModelDriver implements ModelDriverInterface
{
	private $lookupModel;

	public function __construct( $component, $instanceName, $lookupModel )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "wirekitNamespaces" ][ "sitemap" ], "sitemap:hierarchy" );
		$this->appendChild( $this->rootElement );

		$this->lookupModel = $lookupModel;

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
			$componentParts = explode( "\\", $parentNodeList->item( 0 )->nodeValue );
			$parentInstanceName = array_pop( $componentParts );
			$parentComponent = implode( "\\", $componentParts );

			$parentNodeList = $this->lookupModel->xPath->query( "//lookup:entry[ lookup:component = '" . $parentComponent . "' and lookup:instance-name = '" . $parentInstanceName . "' ]" );

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