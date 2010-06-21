<?php

namespace System\Libraries;

class FileSystem
{
	const FS_FILE = 1;
	const FS_DIR = 2;

	const FS_PERM_EXEC	= 0x0001;
	const FS_PERM_WRITE = 0x0002;
	const FS_PERM_READ	= 0x0004;

	public static function createFolderStructure( $folder )
	{
		$folderParts = explode( "/", $folder );

		for( $i = 2; $i < count( $folderParts ); $i++ )
		{
			$builtPath = implode( "/", array_slice( $folderParts, 0, $i ) );

			if( ! self::pathExists( $builtPath ) )
			{
				mkdir( $builtPath );
			}
		}
	}

	public static function testPermissions( $folder, $accessType )
	{
		if( self::pathExists( $folder ) )
		{
			$perms = fileperms( $folder );

			$world = ( $accessType << 6 );
			$group = ( $accessType << 3 );
			$owner = ( $accessType << 0 );

			$hasPerms = ( $perms & $world ) == $world |
						( $perms & $group ) == $group |
						( $perms & $owner ) == $owner;

			return $hasPerms;
		}
		else
		{
			return false;
		}
	}

	public static function deleteFolder( $dirname, $force = false )
	{
		if( $force )
			self::emptyFolder( $dirname );

		return rmdir( $dirname );
	}

	public static function emptyFolder( $folder, $ignore = array() )
	{
		if( self::pathExists( $folder ) )
		{
			$directoryIterator = new \DirectoryIterator( $folder );

			foreach( $directoryIterator as $fileInfo )
			{
				if( !$fileInfo->isDot() )
				{
					$filename = str_replace( "\\", "/", $fileInfo->getPathname() );

					if( !in_array( $filename, $ignore ) )
					{
						if( $fileInfo->isDir() )
						{
							self::emptyFolder( $filename );
							rmdir( $filename );
						}
						else
						{
							unlink( $filename );
						}
					}
				}
			}

			return true;
		}
		else
		{
			return false;
		}
	}

	public static function move( $oldname, $newname )
	{
		return rename( $oldname, $newname );
	}

	public static function pathExists( $path )
	{
		if( $path !== false )
		{
			return self::fileExists( $path );
		}

		return false;
	}

	public static function fileExists( $filename )
	{
		return file_exists( $filename );
	}

	public static function fileGetContentsUTF8( $filename )
	{
		$contents = file_get_contents( Normalize::filename( $filename ) );

		return mb_convert_encoding( $contents, "UTF-8", mb_detect_encoding( $contents, "UTF-8, ISO-8859-1", true ) );
	}

	public static function filePutContents( $filename, $data, $flags = 0, $context = null )
	{
		file_put_contents( Normalize::filename( $filename ), $data, $flags, $context );
	}

	public static function getFolderList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$list[ $rootFolder ] = self::getMeta( $rootFolder, $getMeta );

		$folders = self::getList( $rootFolder, $match, $getMeta, self::FS_DIR );

		$list[ $rootFolder ][ ":FOLDERS:" ] = $folders;

		return $list;
	}

	public static function getFolderListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$list[ $rootFolder ] = self::getMeta( $rootFolder, $getMeta );

		$folderList = self::getFolderListRecursively( $rootFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

		$list[ $rootFolder ][ ":FOLDERS:" ] = $folderList[ ":FOLDERS:" ];

		return $list;
	}

	private static function getFolderListRecursively( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$folders = self::getFolderList( $rootFolder, $match, $getMeta );

		$list[ ":FOLDERS:" ] = $folders[ $rootFolder ][ ":FOLDERS:" ];

		if( count( $folders ) )
		{
			if( $currentDepth < $maxDepth || is_null( $maxDepth ) )
			{
				$folderKeys = array_keys( $list[ ":FOLDERS:" ] );

				foreach( $folderKeys as $subFolder )
				{
					$subFolders = self::getFolderListRecursively( $subFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

					$list[ ":FOLDERS:" ][ $subFolder ][ ":FOLDERS:" ] = $subFolders[ ":FOLDERS:" ];
				}
			}
		}

		return $list;
	}

	public static function getFileList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$list[ $rootFolder ] = self::getMeta( $rootFolder, $getMeta );

		$files = self::getList( $rootFolder, $match, $getMeta, self::FS_FILE );

		$list[ $rootFolder ][ ":FILES:" ] = $files;

		return $list;
	}

	public static function getDirList( $rootFolder, $fileMatch = "/./", $folderMatch = "/./", $getMeta = true )
	{
		$list = array();

		$list[ $rootFolder ] = self::getMeta( $rootFolder, $getMeta );

		$files		= self::getFileList( $rootFolder, $fileMatch, $getMeta );
		$folders	= self::getFolderList( $rootFolder, $folderMatch, $getMeta );

		$list[ $rootFolder ][ ":FILES:" ]	= $files[ $rootFolder ][ ":FILES:" ];
		$list[ $rootFolder ][ ":FOLDERS:" ]	= $folders[ $rootFolder ][ ":FOLDERS:" ];

		return $list;
	}

	public static function getDirListRecursive( $rootFolder, $fileMatch = "/./", $folderMatch = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$list[ $rootFolder ] = self::getMeta( $rootFolder, $getMeta );

		$fileList = self::getDirListRecursively( $rootFolder, $fileMatch, $folderMatch, $getMeta, $maxDepth, $currentDepth + 1 );

		$list[ $rootFolder ][ ":FILES:" ]	= $fileList[ ":FILES:" ];
		$list[ $rootFolder ][ ":FOLDERS:" ]	= $fileList[ ":FOLDERS:" ];

		return $list;
	}

	private static function getDirListRecursively( $rootFolder, $fileMatch = "/./", $folderMatch = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$files		= self::getFileList( $rootFolder, $fileMatch, $getMeta );
		$folders	= self::getFolderList( $rootFolder, $folderMatch, $getMeta );

		$list[ ":FILES:" ]		= $files[ $rootFolder ][ ":FILES:" ];
		$list[ ":FOLDERS:" ]	= $folders[ $rootFolder ][ ":FOLDERS:" ];

		if( count( $folders ) )
		{
			if( $currentDepth < $maxDepth || is_null( $maxDepth ) )
			{
				$folderKeys = array_keys( $list[ ":FOLDERS:" ] );

				foreach( $folderKeys as $subFolder )
				{
					$subFolderFiles = self::getDirListRecursively( $subFolder, $fileMatch, $folderMatch, $getMeta, $maxDepth, $currentDepth + 1 );

					$list[ ":FOLDERS:" ][ $subFolder ][ ":FILES:" ]		= $subFolderFiles[ ":FILES:" ];
					$list[ ":FOLDERS:" ][ $subFolder ][ ":FOLDERS:" ]	= $subFolderFiles[ ":FOLDERS:" ];
				}
			}
		}

		return $list;
	}

	public static function flattenDirListIntoFileList( $list )
	{
		$flatList = array();

		if( isset( $list[ ":FILES:" ] ) && is_array( $list[ ":FILES:" ] ) )
		{
			$fileKeys = array_keys( $list[ ":FILES:" ] );

			foreach( $fileKeys as $file )
			{
				$flatList[] = $file;
			}
		}

		if( isset( $list[ ":FOLDERS:" ] ) && is_array( $list[ ":FOLDERS:" ] ) )
		{
			foreach( $list[ ":FOLDERS:" ] as $subList )
			{
				$flatList = array_merge( $flatList, self::flattenDirListIntoFileList( $subList ) );
			}
		}

		return $flatList;
	}

	private static function getList( $rootFolder, $match = "/./", $getMeta = true, $type = self::FS_FILE )
	{
		$list = array();

		$rootFolder = Normalize::Path( $rootFolder );

		$dir = dir( $rootFolder );

		while( ( $entry = $dir->read() ) !== false )
		{
			if( $entry != "." && $entry != ".." )
			{
				if( preg_match( $match, $entry ) > 0 )
				{
					$file = $rootFolder . $entry;

					$meta = self::getMeta( $file, $getMeta );

					if( ( $meta[ "is_dir" ] && $type == self::FS_DIR ) || ( $meta[ "is_file" ] && $type == self::FS_FILE ) )
					{
						$list[ $file ] = $meta;
					}
				}
			}
		}

		$dir->close();

		return $list;
	}

	public static function getMeta( $file, $getMeta = true )
	{
		$meta = array();

		$meta[ "is_file" ]	= @is_file( $file );
		$meta[ "is_dir" ]	= @is_dir( $file );

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

			$pathParts					= @pathinfo( $file );

			if( $pathParts[ "basename" ] != "" && $pathParts[ "filename" ] != "" )
			{
				$meta[ "dirname" ]		= $pathParts[ "dirname" ];
				$meta[ "basename" ]		= $pathParts[ "basename" ];
				$meta[ "extension" ]	= isset( $pathParts[ "extension" ] ) ? $pathParts[ "extension" ] : "";
				$meta[ "filename" ]		= $pathParts[ "filename" ];
				$meta[ "fullfilename" ]	= $pathParts[ "dirname" ] . "/" . $pathParts[ "basename" ];
			}

			$meta[ "fileatime-nice" ]	= date( "Y-m-d H:i:s", $meta[ "fileatime" ] );
			$meta[ "filectime-nice" ]	= date( "Y-m-d H:i:s", $meta[ "filectime" ] );
			$meta[ "filemtime-nice" ]	= date( "Y-m-d H:i:s", $meta[ "filemtime" ] );
			$meta[ "filesize-nice" ]	= number_format( $meta[ "filesize" ] );
		}

		return $meta;
	}
}