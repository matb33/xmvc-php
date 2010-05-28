<?php

namespace xMVC\Mod\WireKit;

class Combiner
{
	public static function DummyMethod()
	{
		// This method is here as a hack to have PHP load this class, because calling php:function in XSLT without
		// first having this class loaded won't work, despite autoloading mechanisms being in place.  This method
		// is intended to be called in some WireKit core area and essentially do nothing.
	}

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