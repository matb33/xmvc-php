<?php

require_once( SYS_PATH . "libraries/filesystem.php" );

class FileSystemModelDriver extends ModelDriver
{
	var $fileSystem;

	function FileSystemModelDriver()
	{
		parent::ModelDriver();

		$this->fileSystem = new FileSystem();
	}

	function __PushResultsToModel( $listing )
	{
		$query = new Model( "xml" );

		$query->xml->Load( "filesystem", array( "listing" => $listing ) );

		$xmlData = $query->xml->LoadModelXML();

		$this->SetXML( $this->GetXML( true ) . $xmlData );

		return( $xmlData );
	}

	function GetFolderList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$listing = $this->fileSystem->GetFolderList( $rootFolder, $match, $getMeta );

		return( $this->__PushResultsToModel( $listing ) );
	}

	function GetFolderListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null )
	{
		$listing = $this->fileSystem->GetFolderListRecursive( $rootFolder, $match, $getMeta, $maxDepth );

		return( $this->__PushResultsToModel( $listing ) );
	}

	function GetFileList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$listing = $this->fileSystem->GetFileList( $rootFolder, $match, $getMeta );

		return( $this->__PushResultsToModel( $listing ) );
	}

	function GetDirList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$listing = $this->fileSystem->GetDirList( $rootFolder, $match, $getMeta );

		return( $this->__PushResultsToModel( $listing ) );
	}

	function GetDirListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null )
	{
		$listing = $this->fileSystem->GetDirListRecursive( $rootFolder, $match, $getMeta, $maxDepth );

		return( $this->__PushResultsToModel( $listing ) );
	}
}

?>