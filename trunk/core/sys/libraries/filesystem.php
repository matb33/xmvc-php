<?php

define( "FS_FILE",	1 );
define( "FS_DIR",	2 );

class FileSystem
{
	function FileSystem()
	{
	}

	function __GetList( $rootFolder, $match = "/./", $getMeta = true, $type = FS_FILE )
	{
		$list = array();

		$rootFolder = $this->NormalizeFolder( $rootFolder );

		$dir = dir( $rootFolder );

		while( ( $entry = $dir->read() ) !== false )
		{
			if( $entry != "." && $entry != ".." )
			{
				if( preg_match( $match, $entry ) > 0 )
				{
					$file = $rootFolder . $entry;

					$meta = $this->__GetMeta( $file, $getMeta );

					if( ( $meta[ "is_dir" ] && $type == FS_DIR ) || ( $meta[ "is_file" ] && $type == FS_FILE ) )
					{
						$list[ $file ] = $meta;
					}
				}
			}
		}

		$dir->close();

		return( $list );
	}

	function __GetMeta( $file, $getMeta = true )
	{
		$meta = array();

		$meta[ "is_file" ]				= @is_file( $file );
		$meta[ "is_dir" ]				= @is_dir( $file );

		if( $getMeta )
		{
			$meta[ "filesize" ]			= @filesize( $file );
			$meta[ "fileinode" ]		= @fileinode( $file );
			$meta[ "fileatime" ]		= @fileatime( $file );
			$meta[ "filectime" ]		= @filectime( $file );
			$meta[ "filemtime" ]		= @filemtime( $file );
			$meta[ "fileowner" ]		= @fileowner( $file );
			$meta[ "fileperms" ]		= @fileperms( $file );
			$meta[ "filetype" ]			= @filetype( $file );
			$meta[ "is_executable" ]	= @is_executable( $file );
			$meta[ "is_link" ]			= @is_link( $file );
			$meta[ "is_readable" ]		= @is_readable( $file );
			$meta[ "is_uploaded_file" ]	= @is_uploaded_file( $file );
			$meta[ "is_writable" ]		= @is_writable( $file );
		}

		return( $meta );
	}

	function GetFolderList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$list[ $rootFolder ] = $this->__GetMeta( $rootFolder, $getMeta );

		$folders = $this->__GetList( $rootFolder, $match, $getMeta, FS_DIR );

		$list[ $rootFolder ][ ":FOLDERS:" ] = $folders;

		return( $list );
	}

	function GetFolderListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$list[ $rootFolder ] = $this->__GetMeta( $rootFolder, $getMeta );

		$folderList = $this->__GetFolderListRecursive( $rootFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

		$list[ $rootFolder ][ ":FOLDERS:" ] = $folderList[ ":FOLDERS:" ];

		return( $list );
	}

	function __GetFolderListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$folders = $this->GetFolderList( $rootFolder, $match, $getMeta );

		$list[ ":FOLDERS:" ] = $folders[ $rootFolder ][ ":FOLDERS:" ];

		if( count( $folders ) )
		{
			if( $currentDepth < $maxDepth || is_null( $maxDepth ) )
			{
				foreach( array_keys( $list[ ":FOLDERS:" ] ) as $subFolder )
				{
					$subFolders = $this->__GetFolderListRecursive( $subFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

					$list[ ":FOLDERS:" ][ $subFolder ][ ":FOLDERS:" ] = $subFolders[ ":FOLDERS:" ];
				}
			}
		}

		return( $list );
	}

	function GetFileList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$list[ $rootFolder ] = $this->__GetMeta( $rootFolder, $getMeta );

		$files = $this->__GetList( $rootFolder, $match, $getMeta, FS_FILE );

		$list[ $rootFolder ][ ":FILES:" ] = $files;

		return( $list );
	}

	function GetDirList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$list = array();

		$list[ $rootFolder ] = $this->__GetMeta( $rootFolder, $getMeta );

		$files		= $this->GetFileList( $rootFolder, $match, $getMeta );
		$folders	= $this->GetFolderList( $rootFolder, $match, $getMeta );

		$list[ $rootFolder ][ ":FILES:" ]	= $files[ $rootFolder ][ ":FILES:" ];
		$list[ $rootFolder ][ ":FOLDERS:" ]	= $folders[ $rootFolder ][ ":FOLDERS:" ];

		return( $list );
	}

	function GetDirListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$list[ $rootFolder ] = $this->__GetMeta( $rootFolder, $getMeta );

		$fileList = $this->__GetDirListRecursive( $rootFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

		$list[ $rootFolder ][ ":FILES:" ]	= $fileList[ ":FILES:" ];
		$list[ $rootFolder ][ ":FOLDERS:" ]	= $fileList[ ":FOLDERS:" ];

		return( $list );
	}

	function __GetDirListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$files		= $this->GetFileList( $rootFolder, $match, $getMeta );
		$folders	= $this->GetFolderList( $rootFolder, $match, $getMeta );

		$list[ ":FILES:" ]		= $files[ $rootFolder ][ ":FILES:" ];
		$list[ ":FOLDERS:" ]	= $folders[ $rootFolder ][ ":FOLDERS:" ];

		if( count( $folders ) )
		{
			if( $currentDepth < $maxDepth || is_null( $maxDepth ) )
			{
				foreach( array_keys( $list[ ":FOLDERS:" ] ) as $subFolder )
				{
					$subFolderFiles = $this->__GetDirListRecursive( $subFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

					$list[ ":FOLDERS:" ][ $subFolder ][ ":FILES:" ]		= $subFolderFiles[ ":FILES:" ];
					$list[ ":FOLDERS:" ][ $subFolder ][ ":FOLDERS:" ]	= $subFolderFiles[ ":FOLDERS:" ];
				}
			}
		}

		return( $list );
	}

	function NormalizeFolder( $path )
	{
		$path = str_replace( "\\", "/", $path );
		$path = ( substr( $path, -1 ) != "/" ) ? ( $path . "/" ) : $path;

		return( $path );
	}
}

?>
