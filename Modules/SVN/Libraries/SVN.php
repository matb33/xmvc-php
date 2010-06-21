<?php

namespace Modules\SVN\Libraries;

use System\Libraries\Config;
use System\Libraries\FileSystem;
use System\Libraries\Debug;

class SVN
{
	const ADD		= "add";
	//const BLAME	= "blame";
	//const CAT		= "cat";
	const CHECKOUT	= "checkout";
	const CLEANUP	= "cleanup";
	const COMMIT	= "commit";
	//const CP		= "copy";
	const DEL		= "delete";
	//const DIFF	= "diff";
	const EXPORT	= "export";
	//const HELP	= "help";
	const IMPORT	= "import";
	//const INFO	= "info";
	const LS		= "list";
	const LG		= "log";
	const MERGE		= "merge";
	const MD		= "mkdir";
	const MOVE		= "move";
	const PROPDEL	= "propdel";
	const PROPEDIT	= "propedit";
	const PROPGET	= "propget";
	const PROPLIST	= "proplist";
	const PROPSET	= "propset";
	const RESOLVED	= "resolved";
	const REVERT	= "revert";
	const STATUS	= "status";
	const UPDATE	= "update";

	private $respositoryURL;
	private $repositoryPath;
	private $repositoryUsername;
	private $repositoryPassword;
	private $repositoryWorkingFolder;

	public function __construct( $repositoryURL, $repositoryPath, $repositoryUsername, $repositoryPassword, $repositoryWorkingFolder )
	{
		$this->repositoryURL = $repositoryURL;
		$this->repositoryPath = $repositoryPath;
		$this->repositoryUsername = $repositoryUsername;
		$this->repositoryPassword = $repositoryPassword;
		$this->repositoryWorkingFolder = $repositoryWorkingFolder;

		$this->verifyWorkingFolderExists();
		$this->verifyWorkingFolderPermissions();

		$this->verifyConfigFolderExists();
		$this->verifyConfigFolderPermissions();

		$this->verifySVNExecutable();
	}

	public function add( $filename )
	{
		if( FileSystem::pathExists( $filename ) )
		{
			$parameters = array();
			$parameters[] = $filename;

			return $this->execute( self::ADD, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function checkout( $revision = "HEAD" )
	{
		$this->cleanWorkingFolder();

		$parameters = array();
		$parameters[] = $this->repositoryURL . $this->repositoryPath;
		$parameters[] = "\"" . realpath( $this->repositoryWorkingFolder ) . "\"";
		$parameters[] = "--revision " . $revision;
		$parameters[] = "--username " . $this->repositoryUsername;
		$parameters[] = "--password " . $this->repositoryPassword;
		$parameters[] = "--non-interactive";

		return $this->execute( self::CHECKOUT, $parameters );
	}

	public function cleanUp( $path = null )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::pathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = $path;

			return $this->execute( self::CLEANUP, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function commit( $path = null )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::pathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "--username " . $this->repositoryUsername;
			$parameters[] = "--password " . $this->repositoryPassword;
			$parameters[] = "--non-interactive";
			$parameters[] = $path;

			return $this->execute( self::COMMIT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function del( $pathOrUrl )
	{
		$parameters = array();
		$parameters[] = "--username " . $this->repositoryUsername;
		$parameters[] = "--password " . $this->repositoryPassword;
		$parameters[] = "--non-interactive";
		$parameters[] = $pathOrUrl;	// maybe we should check if it's a URL or file, and add surrounding quotes accordingly

		return $this->execute( self::DEL, $parameters );
	}

	public function import( $path )
	{
		if( FileSystem::pathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "--username " . $this->repositoryUsername;
			$parameters[] = "--password " . $this->repositoryPassword;
			$parameters[] = "--non-interactive";
			$parameters[] = "\"" . $path . "\"";
			$parameters[] = $this->repositoryURL . $this->repositoryPath;

			return $this->execute( self::IMPORT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function export( $revision = "HEAD", $path = null, $outputPath = null, $ignoreExternals = false )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::pathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "--username " . $this->repositoryUsername;
			$parameters[] = "--password " . $this->repositoryPassword;
			$parameters[] = "--non-interactive";
			$parameters[] = "--revision " . $revision;

			if( $ignoreExternals )
			{
				$parameters[] = "--ignore-externals";
			}

			$parameters[] = "\"" . $path . "\"";

			if( !is_null( $outputPath ) )
			{
				$parameters[] = "\"" . $outputPath . "\"";
			}

			$parameters[] = $this->repositoryURL . $this->repositoryPath;

			return $this->execute( self::EXPORT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function update( $revision = "HEAD", $path = null )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::pathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "--username " . $this->repositoryUsername;
			$parameters[] = "--password " . $this->repositoryPassword;
			$parameters[] = "--non-interactive";
			$parameters[] = "--revision " . $revision;
			$parameters[] = "\"" . $path . "\"";

			return $this->execute( self::UPDATE, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function revert( $path )
	{
		if( FileSystem::pathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "\"" . $path . "\"";

			return $this->execute( self::REVERT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function ls( $revision = "HEAD" )
	{
		$parameters = array();
		$parameters[] = "--username " . $this->repositoryUsername;
		$parameters[] = "--password " . $this->repositoryPassword;
		$parameters[] = "--non-interactive";
		$parameters[] = "--revision " . $revision;

		return $this->execute( self::LS, $parameters );
	}

	public function lg()
	{
		return $this->execute( self::LG );
	}

	public function isCheckedOut()
	{
		if( FileSystem::pathExists( $this->repositoryWorkingFolder . "/.svn" ) )
		{
			return true;
		}

		return false;
	}

	private function cleanWorkingFolder()
	{
		if( Config::$data[ "isWindows" ] )
		{
			exec( "attrib -h -r  \"" . $this->repositoryWorkingFolder . "/*.*\" /s /d" );
		}

		return FileSystem::emptyFolder( $this->repositoryWorkingFolder );
	}

	private function execute( $subCommand, $parameters = array() )
	{
		$parameters = array_merge( $this->getCommonParameters(), $parameters );

		$command  = realpath( Config::$data[ "svnExecutable" ] ) . " ";
		$command .= $subCommand . " ";
		$command .= implode( " ", $parameters ) . " ";
		$command .= "2>&1";

		$command = str_replace( "\\", "/", $command );

		exec( $command, $output, $returnVar );

		$outputGlued = implode( "\n", $output );

		Debug::write( "Execute command", $command );

		SVNErrors::analyze( $outputGlued, $subCommand );

		return $outputGlued;
	}

	private function getCommonParameters()
	{
		$parameters = array();
		$parameters[] = "--config-dir \"" . realpath( Config::$data[ "svnConfigFolder" ] ) . "\"";

		return $parameters;
	}

	private function verifyWorkingFolderExists()
	{
		FileSystem::createFolderStructure( $this->repositoryWorkingFolder );
	}

	private function verifyWorkingFolderPermissions()
	{
		if( !FileSystem::testPermissions( $this->repositoryWorkingFolder, FileSystem::FS_PERM_READ + FileSystem::FS_PERM_WRITE ) )
		{
			trigger_error( "SVN error: The SVN working folder [" . $this->repositoryWorkingFolder . "] could not be read and/or written. Read and write permissions must be enabled for the web user in order for this SVN module to function.", E_USER_ERROR );
		}
	}

	private function verifyConfigFolderExists()
	{
		FileSystem::createFolderStructure( Config::$data[ "svnConfigFolder" ] );
	}

	private function verifyConfigFolderPermissions()
	{
		if( !FileSystem::testPermissions( Config::$data[ "svnConfigFolder" ], FileSystem::FS_PERM_READ + FileSystem::FS_PERM_WRITE ) )
		{
			trigger_error( "SVN error: The SVN config folder [" . Config::$data[ "svnConfigFolder" ] . "] could not be read and/or written. Read and write permissions must be enabled for the web user in order for this SVN module to function.", E_USER_ERROR );
		}
	}

	private function verifySVNExecutable()
	{
		if( !FileSystem::pathExists( Config::$data[ "svnExecutable" ] ) )
		{
			trigger_error( "SVN error: Could not find SVN binary at [" . Config::$data[ "svnExecutable" ] . "]. Either it does not exist or the specified path is incorrect. Use the config variable \$svnExecutable to define it.", E_USER_ERROR );
		}
	}
}