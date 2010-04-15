<?php

namespace xMVC\Mod\Utils;

use xMVC\Sys\Core;

class DOMUtils
{
	public static function ConvertStringHTMLToDOM( &$model, $xPath )
	{
		foreach( $model->xPath->query( $xPath ) as $node )
		{
			$importDocument = new \DOMDocument();
			$importDocument->loadXML( "<html xmlns=\"http://www.w3.org/1999/xhtml\">" . $node->nodeValue . "</html>" );
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