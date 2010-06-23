<?php

namespace Modules\Utils\Libraries;

use System\Libraries\Core;

class DOMUtils
{
	public static function convertStringHTMLToDOM( &$model, $xPath )
	{
		$nodeList = $model->xPath->query( $xPath );

		foreach( $nodeList as $node )
		{
			$importDocument = new \DOMDocument();
			$importDocument->loadXML( "<html xmlns=\"http://www.w3.org/1999/xhtml\">" . $node->nodeValue . "</html>" );
			$htmlNode = $model->importNode( $importDocument->documentElement, true );
			$node->appendChild( $htmlNode );
		}
	}

	public static function replaceNodeWithChildren( &$refNode, &$node )
	{
		$childNodes = array();

		for( $i = 0; $i < $node->childNodes->length; $i++ )
		{
			$childNodes[] = $node->childNodes->item( $i );
		}

		foreach( $childNodes as $childNode )
		{
			$refNode->parentNode->insertBefore( $childNode, $refNode );
		}

		$refNode->parentNode->removeChild( $refNode );
	}
}