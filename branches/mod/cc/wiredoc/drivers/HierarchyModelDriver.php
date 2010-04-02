<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\ModelDriverInterface;
use xMVC\Sys\Config;
use xMVC\Mod\Language\Language;

class HierarchyModelDriver extends ModelDriver implements ModelDriverInterface
{
	private $sitemapModel;

	public function __construct( $component, $instanceName )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:hierarchy" );
		$this->appendChild( $this->rootElement );

		$this->sitemapModel = Sitemap::Get( Language::GetLang() );

		$this->TransformForeignToXML( $component, $instanceName );
	}

	public function TransformForeignToXML()
	{
		$component = func_get_arg( 0 );
		$instanceName = func_get_arg( 1 );
		$componentInstance = $component . "/" . $instanceName;

		$urlNodeList = $this->sitemapModel->xPath->query( "//s:url[ sitemap:component = '" . $component . "' and sitemap:instance-name = '" . $instanceName . "' ]" );

		if( $urlNodeList->length > 0 )
		{
			$urlNode = $urlNodeList->item( 0 );
			$this->AddHierarchyEntry( $urlNode );
			$this->CrawlSitemapFollowingParentsOf( $urlNode );
		}

		parent::TransformForeignToXML();
	}

	private function CrawlSitemapFollowingParentsOf( $urlNode )
	{
		$parentNodeList = $this->sitemapModel->xPath->query( "sitemap:parent", $urlNode );

		if( $parentNodeList->length > 0 )
		{
			list( $parentComponent, $parentInstanceName ) = explode( "/", $parentNodeList->item( 0 )->nodeValue );
			$parentUrlNodeList = $this->sitemapModel->xPath->query( "//s:url[ sitemap:component = '" . $parentComponent . "' and sitemap:instance-name = '" . $parentInstanceName . "' ]" );

			if( $parentUrlNodeList->length > 0 )
			{
				$parentUrlNode = $parentUrlNodeList->item( 0 );
				$this->AddHierarchyEntry( $parentUrlNode );
				$this->CrawlSitemapFollowingParentsOf( $parentUrlNode );
			}
		}

	}

	private function AddHierarchyEntry( $urlNode )
	{
		$path = $this->sitemapModel->xPath->query( "sitemap:path", $urlNode )->item( 0 )->nodeValue;

		$node = $this->createElementNS( Config::$data[ "ccNamespaces" ][ "sitemap" ], "sitemap:path" );
		$data = $this->createCDATASection( ( string )$path );
		$node->appendChild( $data );
		$this->rootElement->appendChild( $node );
	}
}

?>