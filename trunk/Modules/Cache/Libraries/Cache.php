<?php

namespace Modules\Cache\Libraries;

use System\Libraries\FileSystem;
use System\Drivers\XMLModelDriver;
use System\Libraries\Normalize;
use Modules\Utils\Libraries\StringUtils;

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
		$this->hash = $this->getHash();
		$this->tokens[ "hash" ] = $this->hash;
		$this->tokens[ "cacheID" ] = $this->cacheID;
		$this->filename = $this->getFilename( $this->filenamePattern );
	}

	public function read()
	{
		if( extension_loaded( "memcache" ) && false )
		{
			//memcache code
		}
		else
		{
			return $this->unserialize( file_get_contents( $this->filename ) );
		}
	}

	public function write( $data )
	{
		if( $this->prepCacheFolder( $this->filename, $this->purgeCache ) )
		{
			return file_put_contents( $this->filename, $this->serialize( $data ), FILE_TEXT | LOCK_EX );
		}

		return false;
	}

	public function isCached()
	{
		return file_exists( $this->filename );
	}

	public function prepCacheFolder( $filename, $purgeCache = true )
	{
		$cacheFolder = dirname( $filename ) . "/";

		FileSystem::createFolderStructure( $cacheFolder );

		if( FileSystem::testPermissions( $cacheFolder, FileSystem::FS_PERM_WRITE ) )
		{
			if( $purgeCache )
			{
				$filenames = glob( $cacheFolder . $this->cacheID . "--*" );

				foreach( $filenames as $filename )
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

	private function getFilename( $filenamePattern )
	{
		return StringUtils::replaceTokensInPattern( $filenamePattern, $this->tokens );
	}

	private function getHash()
	{
		$hash = $this->cacheID;

		if( $this->cacheMinutes > 0 )
		{
			$hash .= "--" . md5( $this->cacheID . floor( time() / ( $this->cacheMinutes * 60 ) ) );
		}

		return $hash;
	}

	private function serialize( $data )
	{
		if( $data instanceof XMLModelDriver )
		{
			return $this->manuallyFormatXMLOutput( Normalize::stripRootTag( $data->saveXML() ) );
		}
		else
		{
			return serialize( $data );
		}
	}

	private function unserialize( $data )
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

	private function manuallyFormatXMLOutput( $input )
	{
		// Hack to get XML formatted, despite formatOutput being set in ModelDriver

		$xml = new \DOMDocument( "1.0", "UTF-8" );
		$xml->preserveWhiteSpace = false;
		$xml->formatOutput = true;
		$xml->loadXML( $input );

		return $xml->saveXML();
	}
}