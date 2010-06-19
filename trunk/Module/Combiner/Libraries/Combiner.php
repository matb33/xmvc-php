<?php

namespace Module\Combiner\Libraries;

use System\Libraries\FileSystem;
use System\Libraries\Config;

class Combiner
{
	public static function CombineJavaScripts( $scriptNodes )
	{
		$fileIDs = array();
		$filenames = array();

		foreach( $scriptNodes as $node )
		{
			list( $fileIDs[], $filenames[] ) = self::PrepareFileNames( $node );
		}

		sort( $fileIDs );

		$hash = md5( implode( " ", $fileIDs ) );
		$outputFilename = Config::$data[ "combinerCachePhysicalFolder" ] . "script-" . $hash . ".js";
		$publicFilename = Config::$data[ "combinerCacheWebFolder" ] . "script-" . $hash . ".js";

		self::CombineFiles( $outputFilename, $filenames );

		return $publicFilename;
	}

	public static function CombineStylesheetLinks( $media, $linkNodes )
	{
		$fileIDs = array();
		$filenames = array();

		foreach( $linkNodes as $node )
		{
			list( $fileIDs[], $filenames[] ) = self::PrepareFileNames( $node );
		}

		$hash = md5( implode( " ", $fileIDs ) );
		$outputFilename = Config::$data[ "combinerCachePhysicalFolder" ] . "link-" . $media . "-" . $hash . ".css";
		$publicFilename = Config::$data[ "combinerCacheWebFolder" ] . "link-" . $media . "-" . $hash . ".css";

		self::CombineFiles( $outputFilename, $filenames );

		return $publicFilename;
	}

	private static function CombineFiles( $outputFilename, $filenamesArray )
	{
		if( !FileSystem::FileExists( $outputFilename ) )
		{
			$fileContents = "";
			foreach( $filenamesArray as $file )
			{
				$fileContents .= FileSystem::FileGetContentsUTF8( $file ) . "\n";
			}

			$realOutputFile = Config::$data[ "rootPath" ] . "/" . $outputFilename;
			FileSystem::FilePutContents( $realOutputFile, $fileContents, LOCK_EX );
		}
	}

	private static function PrepareFileNames( $node )
	{
		if( $node->hasAttribute( "href" ) )
		{
			$filename = Config::$data[ "rootPath" ]  . "/" . self::GetPhysicalPath( $node->getAttribute( "href" ) );
			$realPath = realpath( $filename );

			if( $realPath !== false )
			{
				$meta = FileSystem::GetMeta( $realPath );

				$filenames = $meta[ "fullfilename" ];
				$fileIDs = $meta[ "basename" ] . $meta[ "filemtime" ];
			}
			else
			{
				trigger_error( "Combiner could not find file: [" . $filename . "]", E_USER_WARNING );
			}
		}

		return array( $fileIDs, $filenames );
	}

	private static function GetPhysicalPath( $filename )
	{
		$combinerRewriteAdaptors = array( "|^[/]?(.+)/inc/(.*)|" => "./Module/$1/inc/$2", "|^[/]?inc/(.*)$|"  => "./app/inc/$1");

		foreach( $combinerRewriteAdaptors as $pattern => $replacement )
		{
			$filename = preg_replace( $pattern, $replacement, $filename );
		}

		return $filename;
	}
}