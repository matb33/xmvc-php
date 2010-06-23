<?php

namespace System\Drivers;

use System\Libraries\ModelDriver;
use System\Libraries\IModelDriver;
use System\Libraries\View;
use System\Libraries\FileSystem;

class FileSystemModelDriver extends ModelDriver implements IModelDriver
{
	public function __construct()
	{
		parent::__construct();

		$this->rootElement = $this->createElementNS( View::namespaceXML, "xmvc:filesystem" );
		$this->appendChild( $this->rootElement );
	}

	public function getFolderList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::getFolderList( $rootFolder, $match, false );
		$this->transformForeignToXML( $listing );
	}

	public function getDetailedFolderList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::getFolderList( $rootFolder, $match, true );
		$this->transformForeignToXML( $listing );
	}

	public function getFolderListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::getFolderListRecursive( $rootFolder, $match, false, $maxDepth );
		$this->transformForeignToXML( $listing );
	}

	public function getDetailedFolderListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::getFolderListRecursive( $rootFolder, $match, true, $maxDepth );
		$this->transformForeignToXML( $listing );
	}

	public function getFileList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::getFileList( $rootFolder, $match, false );
		$this->transformForeignToXML( $listing );
	}

	public function getDetailedFileList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::getFileList( $rootFolder, $match, true );
		$this->transformForeignToXML( $listing );
	}

	public function getDirList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::getDirList( $rootFolder, $match, false );
		$this->transformForeignToXML( $listing );
	}

	public function getDetailedDirList( $rootFolder, $match = "/./" )
	{
		$listing = FileSystem::getDirList( $rootFolder, $match, true );
		$this->transformForeignToXML( $listing );
	}

	public function getDirListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::getDirListRecursive( $rootFolder, $match, false, $maxDepth );
		$this->transformForeignToXML( $listing );
	}

	public function getDetailedDirListRecursive( $rootFolder, $match = "/./", $maxDepth = null )
	{
		$listing = FileSystem::getDirListRecursive( $rootFolder, $match, true, $maxDepth );
		$this->transformForeignToXML( $listing );
	}

	public function transformForeignToXML()
	{
		$listing = func_get_arg( 0 );

		$this->recursiveListing( $listing );

		parent::transformForeignToXML();
	}

	private function recursiveListing( $listing )
	{
		$listingKeys = array_keys( $listing );

		foreach( $listingKeys as $folderName )
		{
			$folderElement = $this->createElementNS( View::namespaceXML, "xmvc:folder" );
			$nameAttribute = $this->createAttribute( "name" );
			$nameAttribute->value = $folderName;
			$folderElement->appendChild( $nameAttribute );
			$this->rootElement->appendChild( $folderElement );

			foreach( $listing[ $folderName ] as $metaName => $metaData )
			{
				if( $metaName != ":FOLDERS:" && $metaName != ":FILES:" )
				{
					$metaElement = $this->createElementNS( View::namespaceXML, "xmvc:meta" );
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
				$this->recursiveListing( $listing[ $folderName ][ ":FOLDERS:" ] );
			}

			if( isset( $listing[ $folderName ][ ":FILES:" ] ) && count( $listing[ $folderName ][ ":FILES:" ] ) )
			{
				foreach( $listing[ $folderName ][ ":FILES:" ] as $filename => $meta )
				{
					$fileElement = $this->createElementNS( View::namespaceXML, "xmvc:file" );
					$nameAttribute = $this->createAttribute( "name" );
					$nameAttribute->value = $filename;
					$fileElement->appendChild( $nameAttribute );
					$folderElement->appendChild( $fileElement );

					foreach( $meta as $metaName => $metaData )
					{
						$metaElement = $this->createElementNS( View::namespaceXML, "xmvc:meta" );
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