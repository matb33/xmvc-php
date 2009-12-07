<?php

class FileSystemModelDriver extends ModelDriver
{
	public function __construct()
	{
		parent::__construct();
	}

	public function GetFolderList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$listing = FileSystem::GetFolderList( $rootFolder, $match, $getMeta );

		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetFolderListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null )
	{
		$listing = FileSystem::GetFolderListRecursive( $rootFolder, $match, $getMeta, $maxDepth );

		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetFileList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$listing = FileSystem::GetFileList( $rootFolder, $match, $getMeta );

		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDirList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$listing = FileSystem::GetDirList( $rootFolder, $match, $getMeta );

		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDirListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null )
	{
		$listing = FileSystem::GetDirListRecursive( $rootFolder, $match, $getMeta, $maxDepth );

		return( $this->PushResultsToModel( $listing ) );
	}

	private function PushResultsToModel( $listing )
	{
		$query = new Model( "xml" );

		$query->xml->Load( "filesystem", array( "listing" => $listing ) );

		$this->SetXML( $query->xml->GetXML( true ) );

		return( $this->GetXML( false ) );
	}
}

?>