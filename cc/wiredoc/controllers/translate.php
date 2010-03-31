<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\OutputHeaders;
use xMVC\Sys\XMLModelDriver;

class Translate
{
	public function Lang( $lang = null )
	{
		$rootLang = "en";

		if( ! is_null( $lang ) )
		{
			OutputHeaders::Custom( "Content-type: text/plain; charset=UTF-8" );

			$files = array_merge( glob( "app/models/*/*.xpg" ), glob( "app/models/*/*.xwf" ) );

			foreach( $files as $file )
			{
				$model = new XMLModelDriver( $file );

				$model = CC::RegisterNamespaces( $model );

				foreach( $model->xPath->query( "//*[ @lang = '" . $lang . "' ]" ) as $node )
				{
					$thisValue = $node->nodeValue;

					foreach( $model->xPath->query( "../" . $node->nodeName . "[ @lang = '" . $rootLang . "' ]", $node ) as $matchingNode )
					{
						if( $thisValue == $matchingNode->nodeValue )
						{
							echo( "EN: " . $matchingNode->nodeValue . "\n" );
							echo( "FR: " . "\n\n" );
						}
					}
				}
			}
		}
	}
}

?>