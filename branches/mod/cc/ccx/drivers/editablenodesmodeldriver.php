<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\ModelDriver;
use xMVC\Sys\ModelDriverInterface;
use xMVC\Sys\Core;
use xMVC\Sys\Loader;
use xMVC\Sys\Config;
use xMVC\Sys\XMLModelDriver;

class EditableNodesModelDriver extends ModelDriver implements ModelDriverInterface
{
	public function __construct( $container, $content )
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:editable-nodes" );
		$this->appendChild( $this->rootElement );
		$this->rootElement->setAttribute( "xmlns", "http://www.w3.org/1999/xhtml" );

		$this->TransformForeignToXML( $container, $content );
	}

	public function TransformForeignToXML()
	{
		$container = func_get_arg( 0 );
		$content = func_get_arg( 1 );

		foreach( glob( Config::$data[ "svnWorkingFolder" ] . "/" . $container . "/" . $content . ".ccx" ) as $ccxFile )
		{
			$ccxModel = new XMLModelDriver( $ccxFile );

			foreach( $ccxModel->xPath->query( "//*[ ./@editable = '1' ]" ) as $editableNode )
			{
				$node = $this->createElementNS( Config::$data[ "ccNamespace" ], "cc:node" );

				$nameAttribute = $this->createAttribute( "name" );
				$nameAttribute->value = $editableNode->nodeName;
				$node->appendChild( $nameAttribute );

				$localNameAttribute = $this->createAttribute( "local-name" );
				$localNameAttribute->value = $editableNode->localName;
				$node->appendChild( $localNameAttribute );

				$xhtmlAttribute = $this->createAttribute( "is-xhtml" );
				$xhtmlAttribute->value = ( $ccxModel->xPath->query( ".//xhtml:*", $editableNode )->length > 0 ? "1" : "0" );
				$node->appendChild( $xhtmlAttribute );

				$langAttribute = $this->createAttribute( "lang" );
				$langAttribute->value = $editableNode->getAttribute( "lang" );
				$node->appendChild( $langAttribute );

				$xpathAttribute = $this->createAttribute( "xpath" );
				$xpathAttribute->value = $this->ConstructXPath( $ccxModel, $editableNode );
				$node->appendChild( $xpathAttribute );

				$friendlyPathAttribute = $this->createAttribute( "friendly-path" );
				$friendlyPathAttribute->value = $this->ConstructFriendlyPath( $ccxModel, $editableNode );
				$node->appendChild( $friendlyPathAttribute );

				$newNode = $this->importNode( $editableNode, true );
				$node->appendChild( $newNode );

				$this->rootElement->appendChild( $node );
			}
		}

		parent::TransformForeignToXML();
	}

	private function ConstructXPath( $document, $node, $stack = "" )
	{
		$index = $document->xPath->query( "./preceding-sibling::" . $node->nodeName, $node )->length + 1;
		$stack = "/" . $node->nodeName . "[" . $index . "]" . $stack;

		if( $node->parentNode->tagName != "xmvc:root" )
		{
			$stack = $this->ConstructXPath( $document, $node->parentNode, $stack );
		}
		else
		{
			$stack = "/xmvc:root[1]" . $stack;
		}

		return( $stack );
	}

	private function ConstructFriendlyPath( $document, $node, $stack = "" )
	{
		$count = $document->xPath->query( "../" . $node->nodeName, $node )->length;
		$name = $node->localName;

		if( $count > 1 )
		{
			$index = $document->xPath->query( "./preceding-sibling::" . $node->nodeName, $node )->length + 1;
			$name .= " #" . $index;
		}

		if( $node->parentNode->tagName != "xmvc:root" )
		{
			$stack = " › " . $name . $stack;
			$stack = $this->ConstructFriendlyPath( $document, $node->parentNode, $stack );
		}
		else
		{
			$stack = $name . $stack;
		}

		return( $stack );
	}
}

?>