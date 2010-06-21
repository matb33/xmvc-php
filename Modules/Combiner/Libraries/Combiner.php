<?php

namespace Modules\Combiner\Libraries;

use System\Libraries\FileSystem;
use System\Libraries\Config;

class Combiner
{
	public static function combineJavaScripts( $scriptNodes )
	{
		$fileIDs = array();
		$filenames = array();

		foreach( $scriptNodes as $node )
		{
			list( $fileIDs[], $filenames[] ) = self::prepareFileNames( $node );
		}

		sort( $fileIDs );

		$hash = md5( implode( " ", $fileIDs ) );
		$outputFilename = Config::$data[ "combinerCachePhysicalFolder" ] . "script-" . $hash . ".js";
		$publicFilename = Config::$data[ "combinerCacheWebFolder" ] . "script-" . $hash . ".js";

		self::combineFiles( $outputFilename, $filenames );

		return $publicFilename;
	}

	public static function combineStylesheetLinks( $media, $linkNodes )
	{
		$fileIDs = array();
		$filenames = array();

		foreach( $linkNodes as $node )
		{
			list( $fileIDs[], $filenames[] ) = self::prepareFileNames( $node );
		}

		$hash = md5( implode( " ", $fileIDs ) );
		$outputFilename = Config::$data[ "combinerCachePhysicalFolder" ] . "link-" . $media . "-" . $hash . ".css";
		$publicFilename = Config::$data[ "combinerCacheWebFolder" ] . "link-" . $media . "-" . $hash . ".css";

		self::combineFiles( $outputFilename, $filenames );

		return $publicFilename;
	}

	private static function combineFiles( $outputFilename, $filenamesArray )
	{
		if( !FileSystem::fileExists( $outputFilename ) )
		{
			$fileContents = "";
			foreach( $filenamesArray as $file )
			{
				$fileContents .= FileSystem::fileGetContentsUTF8( $file ) . "\n";
			}

			$realOutputFile = Config::$data[ "rootPath" ] . "/" . $outputFilename;
			FileSystem::filePutContents( $realOutputFile, $fileContents, LOCK_EX );
		}
	}

	private static function prepareFileNames( $node )
	{
		if( $node->hasAttribute( "href" ) )
		{
			$filename = Config::$data[ "rootPath" ]  . "/" . self::getPhysicalPath( $node->getAttribute( "href" ) );
			$realPath = realpath( $filename );

			if( $realPath !== false )
			{
				$meta = FileSystem::getMeta( $realPath );

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

	private static function getPhysicalPath( $filename )
	{
		$combinerRewriteAdaptors = Config::$data[ "combinerRewriteAdaptors" ];

		foreach( $combinerRewriteAdaptors as $pattern => $replacement )
		{
			$filename = preg_replace( $pattern, $replacement, $filename );
		}

		return $filename;
	}
}