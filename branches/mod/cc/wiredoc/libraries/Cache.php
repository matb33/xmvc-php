<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\FileSystem;

class Cache
{
	public static function PrepCacheFolder( $cacheFile, $deletePattern = null )
	{
		$cacheFolder = dirname( $cacheFile ) . "/";

		FileSystem::CreateFolderStructure( $cacheFolder );

		if( FileSystem::TestPermissions( $cacheFolder, FileSystem::FS_PERM_WRITE ) )
		{
			if( !is_null( $deletePattern ) )
			{
				foreach( glob( $cacheFolder . $deletePattern ) as $filename )
				{
					unlink( $filename );
				}
			}

			return( true );
		}
		else
		{
			trigger_error( "Write permissions are needed on " . $cacheFolder . " in order to use caching features.", E_USER_NOTICE );
		}

		return( false );
	}
}