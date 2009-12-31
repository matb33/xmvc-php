<?php

namespace Module\ContentLAB;

class Dynamic_nav
{
	public function __construct( &$instance, &$view, $definition, $instanceName )
	{
		Sitemap::LoadSiteMap();

		$hierarchy = Sitemap::GetHierarchy( $definition, $instanceName );

		if( count( $hierarchy ) )
		{
			$startNode = $instance->xPath->query( "//clab:instance[@clab:definition='" . $definition . "' and @clab:instance-name='" . $instanceName . "']" )->item( 0 );

			$this->GenerateInstanceXML( $instance, $hierarchy, $startNode );
		}

		$instance->SetXML( $instance->saveXML() );
	}

	private function GenerateInstanceXML( &$instance, $hierarchy, $startNode )
	{
		$namespaceURI = $startNode->lookupNamespaceURI( "clab" );

		$itemsNode = $instance->createElementNS( $namespaceURI, "clab:items" );
		$itemsNode = $startNode->appendChild( $itemsNode );

		foreach( $hierarchy as $data )
		{
			if( $data[ "visible" ] )
			{
				$itemNode = $instance->createElementNS( $namespaceURI, "clab:item" );
				$itemNode = $itemsNode->appendChild( $itemNode );

				$contentNode = $instance->createElementNS( $namespaceURI, "clab:content" );
				$contentNode = $itemNode->appendChild( $contentNode );

				if( $data[ "clickable" ] )
				{
					$xhtml = "<a href=\"" . htmlentities( $data[ "urlpath" ], ENT_QUOTES, "UTF-8" ) . "\">" . htmlentities( $data[ "name" ], ENT_QUOTES, "UTF-8" ) . "</a>";
				}
				else
				{
					$xhtml = "<span>" . htmlentities( $data[ "name" ], ENT_QUOTES, "UTF-8" ) . "</span>";
				}

				$link = new \DOMDocument();
				$link->loadXML( $xhtml );

				$newNode = $instance->importNode( $link->documentElement, true );
				$newNode = $contentNode->appendChild( $newNode );

				if( isset( $data[ "child_nodes" ] ) )
				{
					$this->GenerateInstanceXML( $instance, $data[ "child_nodes" ], $itemNode );
				}
			}
		}
	}
}

?>