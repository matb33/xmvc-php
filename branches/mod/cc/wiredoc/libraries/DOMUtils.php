<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\Core;

class DOMUtils
{
	public static function ConvertStringHTMLToDOM( &$model, $xPath )
	{
		foreach( $model->xPath->query( $xPath ) as $node )
		{
			$importDocument = new \DOMDocument();
			$importDocument->loadXML( "<xmvc:html-as-xml xmlns:xmvc=\"" . Core::namespaceXML . "\" xmlns=\"http://www.w3.org/1999/xhtml\">" . $node->nodeValue . "</xmvc:html-as-xml>" );
			$htmlNode = $model->importNode( $importDocument->documentElement, true );
			$node->appendChild( $htmlNode );
		}
	}

	public static function ReplaceNodeWithChildren( &$refNode, &$node )
	{
		for( $i = 0; $i < $node->childNodes->length; $i++ )
		{
			$childNode = $node->childNodes->item( $i );

			if( !( $childNode instanceof \DOMText ) )
			{
				$refNode->parentNode->insertBefore( $childNode, $refNode );
			}
		}

		$refNode->parentNode->removeChild( $refNode );
	}
}