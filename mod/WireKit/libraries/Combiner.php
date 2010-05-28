<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\FileSystem;
use xMVC\Sys\Config;

class Combiner
{
	public static function CombineJavaScripts( $scriptNodes )
	{
		$fileIDs = array();
		$filenames = array();

		foreach( $scriptNodes as $node )
		{
			if( $node->hasAttribute( "href" ) )
			{
				// grab the filenames and modified date attributes
				$meta = FileSystem::GetMeta( $node->getAttribute( "href" ) );
				
				$filenames[] = $meta[ "fullfilename" ];
				$fileIDs[] = $meta[ "basename" ] . $meta[ "filemtime" ];
			}
		}

		sort( $fileIDs );

		$hash = md5( implode( " ", $fileIDs ) );
		$outputFilename = Config::$data[ "combinerCachePhysicalFolder" ] . "/" . "script-" . $hash . ".js";
		$publicFilename = Config::$data[ "combinerCacheWebFolder" ] . "/" . "script-" . $hash . ".js";

		if( !FileSystem::FileExists( $outputFilename ) )
		{
			// combine the files
			$fileContents = "";
			foreach( $filenames as $file )
			{
				$fileContents .= FileSystem::FileGetContentsUTF8( $file );
			}

			FileSystem::FilePutContents( $outputFilename, $fileContents );
		}

		var_dump($outputFilename);

		return $publicFilename;
	}

	public static function CombineStylesheetLinks( $media, $linkNodes )
	{
		foreach( $linkNodes as $node )
		{
			if( $node->hasAttribute( "href" ) )
			{
				$files[] = $node->getAttribute( "href" );
			}
		}

		$hash = md5( implode( " ", $files ) );

		return Config::$data[ "combinerCacheWebFolder" ] . "/" . "link-" . $media . "-" . $hash . ".css";
	}
}