<?php

namespace Modules\Flattener\Libraries;

use System\Libraries\FileSystem;

class Flattener
{
	private $outputPath;
	private $indexFilename;

	public function __construct( $outputPath, $indexFilename )
	{
		$this->outputPath = $outputPath;
		$this->indexFilename = $indexFilename;
	}

	public function flattenURL( $url )
	{
		$completeURL = "http://" . $_SERVER[ "HTTP_HOST" ] . $url;

		echo( "Flattening: " . $completeURL . "<br />\n" );

		$contents = $this->getContentsAtURL( $completeURL );

		if( $contents !== false )
		{
			$this->writeContents( $url, $contents );
		}
		else
		{
			echo( "<b>URL doesn't exist. SKIPPED.</b><br />\n" );
		}

		echo "<br />\n";
	}

	private function getContentsAtURL( $url )
	{
		return file_get_contents( $url );
	}

	private function writeContents( $url, $contents )
	{
		$destinationFolder = $this->convertURLToFolderStructure( $url );

		FileSystem::createFolderStructure( $destinationFolder );
		$this->writeContentsToDestinationFolder( $contents, $destinationFolder );
	}

	private function convertURLToFolderStructure( $url )
	{
		$path = str_replace( "\\", "/", $this->outputPath );

		if( substr( $path, -1 ) == "/" )
		{
			$path = substr( $path, 0, -1 );
		}

		$path .= $url;

		return $path;
	}

	private function writeContentsToDestinationFolder( $contents, $folder )
	{
		echo( "<i>Writing contents to " . $folder . $this->indexFilename . "</i><br />\n" );

		file_put_contents( $folder . $this->indexFilename, $contents, FILE_TEXT | LOCK_EX );
	}
}