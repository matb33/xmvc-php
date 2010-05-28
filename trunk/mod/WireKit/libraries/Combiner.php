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
				echo "href = " . $node->getAttribute( "href" );
				echo "\n";
				echo "GetPhysicalPath = " . self::GetPhysicalPath( $node->getAttribute( "href" ) );
				echo "\n";
				echo "Realpath = " . realpath( self::GetPhysicalPath( $node->getAttribute( "href" ) ) );
				echo "\n";
				echo "GetMeta = " . var_dump( FileSystem::GetMeta( realpath( self::GetPhysicalPath( $node->getAttribute( "href" ) ) ) ) );
				echo "\n";

				$meta = FileSystem::GetMeta( realpath( self::GetPhysicalPath( $node->getAttribute( "href" ) ) ) );
				var_dump($meta);
				
				$filenames[] = $meta[ "fullfilename" ];
				$fileIDs[] = $meta[ "basename" ] . $meta[ "filemtime" ];
			}
		}
		exit();

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

	private static function GetPhysicalPath( $filename )
	{
		$combinerRewriteAdaptors = array( "|^[/]?(.+)/inc/(.*)|" => "./mod/$1/inc/$2", "|^[/]?inc/(.*)$|"  => "./app/inc/$1");

		foreach( $combinerRewriteAdaptors as $pattern => $replacement )
		{
			$filename = preg_replace($pattern, $replacement, $filename);
		}

		return $filename;
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