<?php

class FileSystemModelDriver extends ModelDriver implements ModelDriverInterface
{
	private $rootElement;

	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( xMVC::$namespace, "xmvc:filesystem" );
		$this->appendChild( $this->rootElement );
	}

	public function GetFolderList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFolderList( $rootFolder, $match, false );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDetailedFolderList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFolderList( $rootFolder, $match, true );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetFolderListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetFolderListRecursive( $rootFolder, $match, false, $maxDepth );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDetailedFolderListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetFolderListRecursive( $rootFolder, $match, true, $maxDepth );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetFileList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFileList( $rootFolder, $match, false );
		return( $this->TransformForeignToXML( $listing ) );
	}

	public function GetDetailedFileList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFileList( $rootFolder, $match, true );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDirList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetDirList( $rootFolder, $match, false );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDetailedDirList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetDirList( $rootFolder, $match, true );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDirListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetDirListRecursive( $rootFolder, $match, false, $maxDepth );
		return( $this->PushResultsToModel( $listing ) );
	}

	public function GetDetailedDirListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetDirListRecursive( $rootFolder, $match, true, $maxDepth );
		return( $this->PushResultsToModel( $listing ) );
	}

	private function PushResultsToModel( $listing )
	{
	}

	public function TransformForeignToXML()
	{
		$listing = func_get_arg( 0 );

		$this->RecursiveListing( $listing );
	}

	private function RecursiveListing( $listing )
	{
		foreach( array_keys( $listing ) as $folder )
		{
			$folderElement = $this->createElementNS( xMVC::$namespace, "xmvc:folder" );
			$nameAttribute = $this->createAttributeNS( xMVC::$namespace, "xmvc:name" );
			$nameAttribute->value = $folder;

			$folderElement->appendChild( $nameAttribute );
			$this->rootElement->appendChild( $folderElement );

			foreach( $listing[ $folder ] as $name => $data )
			{
				if( $name != ":FOLDERS:" && $name != ":FILES:" )
				{
					$metaElement = $this->createElementNS( xMVC::$namespace, "xmvc:meta" );
					$nameAttribute = $this->createAttributeNS( xMVC::$namespace, "xmvc:name" );
					$nameAttribute->value = $name;
					$metaElement->value = ( string )$data;

					$metaElement->appendChild( $nameAttribute );
					$folderElement->appendChild( $metaElement );
				}
			}

			if( isset( $listing[ $folder ][ ":FOLDERS:" ] ) && count( $listing[ $folder ][ ":FOLDERS:" ] ) )
			{
				RecursiveListing( $listing[ $folder ][ ":FOLDERS:" ] );
			}

			if( isset( $listing[ $folder ][ ":FILES:" ] ) && count( $listing[ $folder ][ ":FILES:" ] ) )
			{
				foreach( $listing[ $folder ][ ":FILES:" ] as $filename => $meta )
				{
					$fileElement = $this->createElementNS( xMVC::$namespace, "xmvc:file" );
					$nameAttribute = $this->createAttributeNS( xMVC::$namespace, "xmvc:name" );
					$nameAttribute->value = $filename;

					$fileElement->appendChild( $nameAttribute );
					$folderElement->appendChild( $fileElement );

					foreach( $meta as $name => $data )
					{
						$metaElement = $this->createElementNS( xMVC::$namespace, "xmvc:meta" );
						$nameAttribute = $this->createAttributeNS( xMVC::$namespace, "xmvc:name" );
						$nameAttribute->value = $name;
						$metaElement->value = ( string )$data;

						$metaElement->appendChild( $nameAttribute );
						$fileElement->appendChild( $metaElement );
					}
				}
			}
		}
	}
}

?>