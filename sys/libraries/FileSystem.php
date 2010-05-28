<?php

namespace xMVC\Sys;

class FileSystem
{
	const FS_FILE = 1;
	const FS_DIR = 2;

	const FS_PERM_EXEC	= 0x0001;
	const FS_PERM_WRITE = 0x0002;
	const FS_PERM_READ	= 0x0004;

	public static function CreateFolderStructure( $folder )
	{
		$folderParts = explode( "/", $folder );

		for( $i = 2; $i < count( $folderParts ); $i++ )
		{
			$builtPath = implode( "/", array_slice( $folderParts, 0, $i ) );

			if( ! self::PathExists( $builtPath ) )
			{
				mkdir( $builtPath );
			}
		}
	}

	public static function TestPermissions( $folder, $accessType )
	{
		if( self::PathExists( $folder ) )
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

	public static function EmptyFolder( $folder, $ignore = array() )
	{
		if( self::PathExists( $folder ) )
		{
			foreach( new \DirectoryIterator( $folder ) as $fileInfo )
			{
				if( !$fileInfo->isDot() )
				{
					$filename = str_replace( "\\", "/", $fileInfo->getPathname() );

					if( !in_array( $filename, $ignore ) )
					{
						if( $fileInfo->isDir() )
						{
							self::EmptyFolder( $filename );
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

	public static function PathExists( $path )
	{
		if( $path !== false )
		{
			return self::FileExists( $path );
		}

		return false;
	}

	public function FileExists( $filename )
	{
		return file_exists( $filename );
	}

	public static function FileGetContentsUTF8( $filename )
	{
		$contents = file_get_contents( Normalize::Filename( $filename ) );

		return mb_convert_encoding( $contents, "UTF-8", mb_detect_encoding( $contents, "UTF-8, ISO-8859-1", true ) );
	}

	public static function FilePutContents( $filename, $data, $flags = 0, $context = null )
	{
		file_put_contents( Normalize::Filename( $filename ), $data, $flags, $context );
	}

	public static function GetFolderList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$list[ $rootFolder ] = self::GetMeta( $rootFolder, $getMeta );

		$folders = self::GetList( $rootFolder, $match, $getMeta, self::FS_DIR );

		$list[ $rootFolder ][ ":FOLDERS:" ] = $folders;

		return $list;
	}

	public static function GetFolderListRecursive( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$list[ $rootFolder ] = self::GetMeta( $rootFolder, $getMeta );

		$folderList = self::GetFolderListRecursively( $rootFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

		$list[ $rootFolder ][ ":FOLDERS:" ] = $folderList[ ":FOLDERS:" ];

		return $list;
	}

	private static function GetFolderListRecursively( $rootFolder, $match = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$folders = self::GetFolderList( $rootFolder, $match, $getMeta );

		$list[ ":FOLDERS:" ] = $folders[ $rootFolder ][ ":FOLDERS:" ];

		if( count( $folders ) )
		{
			if( $currentDepth < $maxDepth || is_null( $maxDepth ) )
			{
				foreach( array_keys( $list[ ":FOLDERS:" ] ) as $subFolder )
				{
					$subFolders = self::GetFolderListRecursively( $subFolder, $match, $getMeta, $maxDepth, $currentDepth + 1 );

					$list[ ":FOLDERS:" ][ $subFolder ][ ":FOLDERS:" ] = $subFolders[ ":FOLDERS:" ];
				}
			}
		}

		return $list;
	}

	public static function GetFileList( $rootFolder, $match = "/./", $getMeta = true )
	{
		$list[ $rootFolder ] = self::GetMeta( $rootFolder, $getMeta );

		$files = self::GetList( $rootFolder, $match, $getMeta, self::FS_FILE );

		$list[ $rootFolder ][ ":FILES:" ] = $files;

		return $list;
	}

	public static function GetDirList( $rootFolder, $fileMatch = "/./", $folderMatch = "/./", $getMeta = true )
	{
		$list = array();

		$list[ $rootFolder ] = self::GetMeta( $rootFolder, $getMeta );

		$files		= self::GetFileList( $rootFolder, $fileMatch, $getMeta );
		$folders	= self::GetFolderList( $rootFolder, $folderMatch, $getMeta );

		$list[ $rootFolder ][ ":FILES:" ]	= $files[ $rootFolder ][ ":FILES:" ];
		$list[ $rootFolder ][ ":FOLDERS:" ]	= $folders[ $rootFolder ][ ":FOLDERS:" ];

		return $list;
	}

	public static function GetDirListRecursive( $rootFolder, $fileMatch = "/./", $folderMatch = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$list[ $rootFolder ] = self::GetMeta( $rootFolder, $getMeta );

		$fileList = self::GetDirListRecursively( $rootFolder, $fileMatch, $folderMatch, $getMeta, $maxDepth, $currentDepth + 1 );

		$list[ $rootFolder ][ ":FILES:" ]	= $fileList[ ":FILES:" ];
		$list[ $rootFolder ][ ":FOLDERS:" ]	= $fileList[ ":FOLDERS:" ];

		return $list;
	}

	private static function GetDirListRecursively( $rootFolder, $fileMatch = "/./", $folderMatch = "/./", $getMeta = true, $maxDepth = null, $currentDepth = 0 )
	{
		$list = array();

		$files		= self::GetFileList( $rootFolder, $fileMatch, $getMeta );
		$folders	= self::GetFolderList( $rootFolder, $folderMatch, $getMeta );

		$list[ ":FILES:" ]		= $files[ $rootFolder ][ ":FILES:" ];
		$list[ ":FOLDERS:" ]	= $folders[ $rootFolder ][ ":FOLDERS:" ];

		if( count( $folders ) )
		{
			if( $currentDepth < $maxDepth || is_null( $maxDepth ) )
			{
				foreach( array_keys( $list[ ":FOLDERS:" ] ) as $subFolder )
				{
					$subFolderFiles = self::GetDirListRecursively( $subFolder, $fileMatch, $folderMatch, $getMeta, $maxDepth, $currentDepth + 1 );

					$list[ ":FOLDERS:" ][ $subFolder ][ ":FILES:" ]		= $subFolderFiles[ ":FILES:" ];
					$list[ ":FOLDERS:" ][ $subFolder ][ ":FOLDERS:" ]	= $subFolderFiles[ ":FOLDERS:" ];
				}
			}
		}

		return $list;
	}

	public static function FlattenDirListIntoFileList( $list )
	{
		$flatList = array();

		if( isset( $list[ ":FILES:" ] ) && is_array( $list[ ":FILES:" ] ) )
		{
			foreach( array_keys( $list[ ":FILES:" ] ) as $file )
			{
				$flatList[] = $file;
			}
		}

		if( isset( $list[ ":FOLDERS:" ] ) && is_array( $list[ ":FOLDERS:" ] ) )
		{
			foreach( $list[ ":FOLDERS:" ] as $subList )
			{
				$flatList = array_merge( $flatList, self::FlattenDirListIntoFileList( $subList ) );
			}
		}

		return $flatList;
	}

	private static function GetList( $rootFolder, $match = "/./", $getMeta = true, $type = self::FS_FILE )
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

					$meta = self::GetMeta( $file, $getMeta );

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

	public static function GetMeta( $file, $getMeta = true )
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
			$meta[ "dirname" ]			= $pathParts[ "dirname" ];
			$meta[ "basename" ]			= $pathParts[ "basename" ];
			$meta[ "extension" ]		= isset( $pathParts[ "extension" ] ) ? $pathParts[ "extension" ] : "";
			$meta[ "filename" ]			= $pathParts[ "filename" ];
			$meta[ "fullfilename" ]		= $pathParts[ "dirname" ] . "/" . $pathParts[ "basename" ];

			$meta[ "fileatime-nice" ]	= date( "Y-m-d H:i:s", $meta[ "fileatime" ] );
			$meta[ "filectime-nice" ]	= date( "Y-m-d H:i:s", $meta[ "filectime" ] );
			$meta[ "filemtime-nice" ]	= date( "Y-m-d H:i:s", $meta[ "filemtime" ] );
			$meta[ "filesize-nice" ]	= number_format( $meta[ "filesize" ] );
		}

		return $meta;
	}
}