<?php

namespace xMVC\Mod\WireKit;

class Combiner
{
	public static function CombineJavaScripts( $basePath, $scriptNodes )
	{
		$files = array();

		foreach( $scriptNodes as $node )
		{
			if( $node->hasAttribute( "href" ) )
			{
				$files[] = $node->getAttribute( "href" );
			}
		}

		$hash = md5( implode( " ", $files ) );

		return $basePath . "script-" . $hash . ".js";
	}

	public static function CombineStylesheetLinks( $basePath, $media, $linkNodes )
	{
		$files = array();

		foreach( $linkNodes as $node )
		{
			if( $node->hasAttribute( "href" ) )
			{
				$files[] = $node->getAttribute( "href" );
			}
		}

		$hash = md5( implode( " ", $files ) );

		return $basePath . "link-" . $media . "-" . $hash . ".css";
	}
}