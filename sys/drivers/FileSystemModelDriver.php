<?php

namespace xMVC\Sys;

class FileSystemModelDriver extends ModelDriver implements IModelDriver
{
	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( Core::namespaceXML, "xmvc:filesystem" );
		$this->appendChild( $this->rootElement );
	}

	public function GetFolderList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFolderList( $rootFolder, $match, false );
		$this->TransformForeignToXML( $listing );
	}

	public function GetDetailedFolderList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFolderList( $rootFolder, $match, true );
		$this->TransformForeignToXML( $listing );
	}

	public function GetFolderListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetFolderListRecursive( $rootFolder, $match, false, $maxDepth );
		$this->TransformForeignToXML( $listing );
	}

	public function GetDetailedFolderListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetFolderListRecursive( $rootFolder, $match, true, $maxDepth );
		$this->TransformForeignToXML( $listing );
	}

	public function GetFileList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFileList( $rootFolder, $match, false );
		$this->TransformForeignToXML( $listing );
	}

	public function GetDetailedFileList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetFileList( $rootFolder, $match, true );
		$this->TransformForeignToXML( $listing );
	}

	public function GetDirList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetDirList( $rootFolder, $match, false );
		$this->TransformForeignToXML( $listing );
	}

	public function GetDetailedDirList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::GetDirList( $rootFolder, $match, true );
		$this->TransformForeignToXML( $listing );
	}

	public function GetDirListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetDirListRecursive( $rootFolder, $match, false, $maxDepth );
		$this->TransformForeignToXML( $listing );
	}

	public function GetDetailedDirListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::GetDirListRecursive( $rootFolder, $match, true, $maxDepth );
		$this->TransformForeignToXML( $listing );
	}

	public function TransformForeignToXML()
	{
		$listing = func_get_arg( 0 );

		$this->RecursiveListing( $listing );

		parent::TransformForeignToXML();
	}

	private function RecursiveListing( $listing )
	{
		foreach( array_keys( $listing ) as $folderName )
		{
			$folderElement = $this->createElementNS( Core::namespaceXML, "xmvc:folder" );
			$nameAttribute = $this->createAttribute( "name" );
			$nameAttribute->value = $folderName;
			$folderElement->appendChild( $nameAttribute );
			$this->rootElement->appendChild( $folderElement );

			foreach( $listing[ $folderName ] as $metaName => $metaData )
			{
				if( $metaName != ":FOLDERS:" && $metaName != ":FILES:" )
				{
					$metaElement = $this->createElementNS( Core::namespaceXML, "xmvc:meta" );
					$nameAttribute = $this->createAttribute( "name" );
					$valueNode = $this->createCDATASection( ( string )$metaData );
					$nameAttribute->value = $metaName;
					$metaElement->appendChild( $valueNode );
					$metaElement->appendChild( $nameAttribute );
					$folderElement->appendChild( $metaElement );
				}
			}

			if( isset( $listing[ $folderName ][ ":FOLDERS:" ] ) && count( $listing[ $folderName ][ ":FOLDERS:" ] ) )
			{
				$this->RecursiveListing( $listing[ $folderName ][ ":FOLDERS:" ] );
			}

			if( isset( $listing[ $folderName ][ ":FILES:" ] ) && count( $listing[ $folderName ][ ":FILES:" ] ) )
			{
				foreach( $listing[ $folderName ][ ":FILES:" ] as $filename => $meta )
				{
					$fileElement = $this->createElementNS( Core::namespaceXML, "xmvc:file" );
					$nameAttribute = $this->createAttribute( "name" );
					$nameAttribute->value = $filename;
					$fileElement->appendChild( $nameAttribute );
					$folderElement->appendChild( $fileElement );

					foreach( $meta as $metaName => $metaData )
					{
						$metaElement = $this->createElementNS( Core::namespaceXML, "xmvc:meta" );
						$nameAttribute = $this->createAttribute( "name" );
						$valueNode = $this->createCDATASection( ( string )$metaData );
						$nameAttribute->value = $metaName;
						$metaElement->appendChild( $valueNode );
						$metaElement->appendChild( $nameAttribute );
						$fileElement->appendChild( $metaElement );
					}
				}
			}
		}
	}
}