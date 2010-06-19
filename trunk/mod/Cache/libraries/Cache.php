<?php

namespace xMVC\Mod\Cache;

use xMVC\Sys\FileSystem;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\Normalize;
use xMVC\Mod\Utils\StringUtils;

class Cache
{
	private $cacheMinutes = 0;
	private $cacheID = "";
	private $tokens = array();
	private $filenamePattern = "";
	private $purgeCache = true;
	private $hash = "";
	private $filename = "";

	public function __construct( $filenamePattern, $tokens, $cacheID = "", $purgeCache = true, $cacheMinutes = 0 )
	{
		$this->cacheMinutes = $cacheMinutes;
		$this->cacheID = $cacheID;
		$this->tokens = $tokens;
		$this->filenamePattern = $filenamePattern;
		$this->purgeCache = $purgeCache;
		$this->hash = $this->GetHash();
		$this->tokens[ "hash" ] = $this->hash;
		$this->tokens[ "cacheID" ] = $this->cacheID;
		$this->filename = $this->GetFilename( $this->filenamePattern );
	}

	public function Read()
	{
		if( extension_loaded( "memcache" ) && false )
		{
			//memcache code
		}
		else
		{
			return $this->Unserialize( file_get_contents( $this->filename ) );
		}
	}

	public function Write( $data )
	{
		if( $this->PrepCacheFolder( $this->filename, $this->purgeCache ) )
		{
			return file_put_contents( $this->filename, $this->Serialize( $data ), FILE_TEXT | LOCK_EX );
		}

		return false;
	}

	public function IsCached()
	{
		return file_exists( $this->filename );
	}

	public function PrepCacheFolder( $filename, $purgeCache = true )
	{
		$cacheFolder = dirname( $filename ) . "/";

		FileSystem::CreateFolderStructure( $cacheFolder );

		if( FileSystem::TestPermissions( $cacheFolder, FileSystem::FS_PERM_WRITE ) )
		{
			if( $purgeCache )
			{
				foreach( glob( $cacheFolder . $this->cacheID . "--*" ) as $filename )
				{
					unlink( $filename );
				}
			}

			return true;
		}
		else
		{
			trigger_error( "Write permissions are needed on " . $cacheFolder . " in order to use caching features.", E_USER_NOTICE );
		}

		return false;
	}

	private function GetFilename( $filenamePattern )
	{
		return StringUtils::ReplaceTokensInPattern( $filenamePattern, $this->tokens );
	}

	private function GetHash()
	{
		$hash = $this->cacheID;

		if( $this->cacheMinutes > 0 )
		{
			$hash .= "--" . md5( $this->cacheID . floor( time() / ( $this->cacheMinutes * 60 ) ) );
		}

		return $hash;
	}

	private function Serialize( $data )
	{
		if( $data instanceof XMLModelDriver )
		{
			return $this->ManuallyFormatXMLOutput( Normalize::StripRootTag( $data->saveXML() ) );
		}
		else
		{
			return serialize( $data );
		}
	}

	private function Unserialize( $data )
	{
		if( strpos( $data, "<?xml" ) !== false )
		{
			return new XMLModelDriver( $data );
		}
		else
		{
			return unserialize( $data );
		}
	}

	private function ManuallyFormatXMLOutput( $input )
	{
		// Hack to get XML formatted, despite formatOutput being set in ModelDriver

		$xml = new \DOMDocument( "1.0", "UTF-8" );
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;
		$xml->loadXML( $input );

		return $xml->saveXML();
	}
}