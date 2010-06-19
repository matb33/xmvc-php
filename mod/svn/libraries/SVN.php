<?php

namespace xMVC\Mod\SVN;

use xMVC\Sys\Config;
use xMVC\Sys\FileSystem;
use xMVC\Sys\Debug;

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

		$this->VerifyWorkingFolderExists();
		$this->VerifyWorkingFolderPermissions();

		$this->VerifyConfigFolderExists();
		$this->VerifyConfigFolderPermissions();

		$this->VerifySVNExecutable();
	}

	public function Add( $filename )
	{
		if( FileSystem::PathExists( $filename ) )
		{
			$parameters = array();
			$parameters[] = $filename;

			return $this->Execute( self::ADD, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function Checkout( $revision = "HEAD" )
	{
		$this->CleanWorkingFolder();

		$parameters = array();
		$parameters[] = $this->repositoryURL . $this->repositoryPath;
		$parameters[] = "\"" . realpath( $this->repositoryWorkingFolder ) . "\"";
		$parameters[] = "--revision " . $revision;
		$parameters[] = "--username " . $this->repositoryUsername;
		$parameters[] = "--password " . $this->repositoryPassword;
		$parameters[] = "--non-interactive";

		return $this->Execute( self::CHECKOUT, $parameters );
	}

	public function CleanUp( $path = null )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::PathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = $path;

			return $this->Execute( self::CLEANUP, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function Commit( $path = null )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::PathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "--username " . $this->repositoryUsername;
			$parameters[] = "--password " . $this->repositoryPassword;
			$parameters[] = "--non-interactive";
			$parameters[] = $path;

			return $this->Execute( self::COMMIT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function Del( $pathOrUrl )
	{
		$parameters = array();
		$parameters[] = "--username " . $this->repositoryUsername;
		$parameters[] = "--password " . $this->repositoryPassword;
		$parameters[] = "--non-interactive";
		$parameters[] = $pathOrUrl;	// maybe we should check if it's a URL or file, and add surrounding quotes accordingly

		return $this->Execute( self::DEL, $parameters );
	}

	public function Import( $path )
	{
		if( FileSystem::PathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "--username " . $this->repositoryUsername;
			$parameters[] = "--password " . $this->repositoryPassword;
			$parameters[] = "--non-interactive";
			$parameters[] = "\"" . $path . "\"";
			$parameters[] = $this->repositoryURL . $this->repositoryPath;

			return $this->Execute( self::IMPORT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function Export( $revision = "HEAD", $path = null, $outputPath = null, $ignoreExternals = false )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::PathExists( $path ) )
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

			return $this->Execute( self::EXPORT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function Update( $revision = "HEAD", $path = null )
	{
		if( is_null( $path ) )
		{
			$path = realpath( $this->repositoryWorkingFolder );
		}

		if( FileSystem::PathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "--username " . $this->repositoryUsername;
			$parameters[] = "--password " . $this->repositoryPassword;
			$parameters[] = "--non-interactive";
			$parameters[] = "--revision " . $revision;
			$parameters[] = "\"" . $path . "\"";

			return $this->Execute( self::UPDATE, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function Revert( $path )
	{
		if( FileSystem::PathExists( $path ) )
		{
			$parameters = array();
			$parameters[] = "\"" . $path . "\"";

			return $this->Execute( self::REVERT, $parameters );
		}
		else
		{
			return false;
		}
	}

	public function Ls( $revision = "HEAD" )
	{
		$parameters = array();
		$parameters[] = "--username " . $this->repositoryUsername;
		$parameters[] = "--password " . $this->repositoryPassword;
		$parameters[] = "--non-interactive";
		$parameters[] = "--revision " . $revision;

		return $this->Execute( self::LS, $parameters );
	}

	public function Lg()
	{
		return $this->Execute( self::LG );
	}

	public function IsCheckedOut()
	{
		if( FileSystem::PathExists( $this->repositoryWorkingFolder . "/.svn" ) )
		{
			return true;
		}

		return false;
	}

	private function CleanWorkingFolder()
	{
		if( Config::$data[ "isWindows" ] )
		{
			exec( "attrib -h -r  \"" . $this->repositoryWorkingFolder . "/*.*\" /s /d" );
		}

		return FileSystem::EmptyFolder( $this->repositoryWorkingFolder );
	}

	private function Execute( $subCommand, $parameters = array() )
	{
		$parameters = array_merge( $this->GetCommonParameters(), $parameters );

		$command  = realpath( Config::$data[ "svnExecutable" ] ) . " ";
		$command .= $subCommand . " ";
		$command .= implode( " ", $parameters ) . " ";
		$command .= "2>&1";

		$command = str_replace( "\\", "/", $command );

		exec( $command, $output, $returnVar );

		$outputGlued = implode( "\n", $output );

		Debug::Write( "Execute command", $command );

		SVNErrors::Analyze( $outputGlued, $subCommand );

		return $outputGlued;
	}

	private function GetCommonParameters()
	{
		$parameters = array();
		$parameters[] = "--config-dir \"" . realpath( Config::$data[ "svnConfigFolder" ] ) . "\"";

		return $parameters;
	}

	private function VerifyWorkingFolderExists()
	{
		FileSystem::CreateFolderStructure( $this->repositoryWorkingFolder );
	}

	private function VerifyWorkingFolderPermissions()
	{
		if( !FileSystem::TestPermissions( $this->repositoryWorkingFolder, FileSystem::FS_PERM_READ + FileSystem::FS_PERM_WRITE ) )
		{
			trigger_error( "SVN error: The SVN working folder [" . $this->repositoryWorkingFolder . "] could not be read and/or written. Read and write permissions must be enabled for the web user in order for this SVN module to function.", E_USER_ERROR );
		}
	}

	private function VerifyConfigFolderExists()
	{
		FileSystem::CreateFolderStructure( Config::$data[ "svnConfigFolder" ] );
	}

	private function VerifyConfigFolderPermissions()
	{
		if( !FileSystem::TestPermissions( Config::$data[ "svnConfigFolder" ], FileSystem::FS_PERM_READ + FileSystem::FS_PERM_WRITE ) )
		{
			trigger_error( "SVN error: The SVN config folder [" . Config::$data[ "svnConfigFolder" ] . "] could not be read and/or written. Read and write permissions must be enabled for the web user in order for this SVN module to function.", E_USER_ERROR );
		}
	}

	private function VerifySVNExecutable()
	{
		if( !FileSystem::PathExists( Config::$data[ "svnExecutable" ] ) )
		{
			trigger_error( "SVN error: Could not find SVN binary at [" . Config::$data[ "svnExecutable" ] . "]. Either it does not exist or the specified path is incorrect. Use the config variable \$svnExecutable to define it.", E_USER_ERROR );
		}
	}
}

?>