<?php

namespace xMVC\Mod\Flattener;

use xMVC\Sys\FileSystem;

class Flattener
{
	private $outputPath;
	private $indexFilename;

	public function __construct()
	{
	}

	public function SetOutputPath( $outputPath )
	{
		$this->outputPath = $outputPath;
	}

	public function SetIndexFilename( $indexFilename )
	{
		$this->indexFilename = $indexFilename;
	}

	public function FlattenURL( $url )
	{
		$completeURL = "http://" . $_SERVER[ "HTTP_HOST" ] . $url;

		echo( "Flattening: " . $completeURL . "<br />\n" );

		$contents = $this->GetContentsAtURL( $completeURL );

		if( $contents !== false )
		{
			$this->WriteContents( $url, $contents );
		}
		else
		{
			echo( "<b>URL doesn't exist. SKIPPED.</b><br />\n" );
		}

		echo "<br />\n";
	}

	private function GetContentsAtURL( $url )
	{
		return( file_get_contents( $url ) );
	}

	private function WriteContents( $url, $contents )
	{
		$destinationFolder = $this->ConvertURLToFolderStructure( $url );

		FileSystem::CreateFolderStructure( $destinationFolder );
		$this->WriteContentsToDestinationFolder( $contents, $destinationFolder );
	}

	private function ConvertURLToFolderStructure( $url )
	{
		$path = str_replace( "\\", "/", self::$outputPath );

		if( substr( $path, -1 ) == "/" )
		{
			$path = substr( $path, 0, -1 );
		}

		$path .= $url;

		return( $path );
	}

	private function WriteContentsToDestinationFolder( $contents, $folder )
	{
		echo( "<i>Writing contents to " . $folder . $this->indexFilename . "</i><br />\n" );

		file_put_contents( $folder . $this->indexFilename, $contents );
	}
}

?>