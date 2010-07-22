<?php

namespace xMVC\Mod\Flattener;

use xMVC\Sys\Config;
use xMVC\Sys\FileSystem;

class WGet
{
	private $outputFolder = "";
	private $properties = array();
	private $verbose = true;
	private $temporaryPath = "";
	private $temporaryRoot = "";

	public function __construct( $outputFolder, $properties = array(), $verbose = true )
	{
		if( !is_null( $properties ) )
			$this->properties = $properties;

		$this->outputFolder = $outputFolder;
		$this->verbose = $verbose;
	}

	public function Execute( $url, $properties = array(), $verbose = true )
	{
		$this->verbose = $verbose;

		$commandParams = array();

		// overwrite properties with passed-in values
		if( !is_null( $properties ) )
			$properties = array_merge( $this->properties,  $properties );

		// gather all parameters
		foreach( $properties as $propertyName => $propertyValue )
		{
			if( is_array( $propertyValue ) )
			{
				$values = "=" . implode(",", $propertyValue);
			}
			elseif( $propertyValue != "" )
			{
				$values = "=" . $propertyValue;
			}
			else
			{
				$values = "";
			}

			$commandParams[] = "--" . $propertyName . $values;
		}

		$commandParams[] = $url;

		$this->SetFolder();

		$command = realpath( Config::$data[ "wgetExecutable" ] ) . " ";
		$command = str_replace( "\\", "/", $command );

		$oldCwd = getcwd();
		$this->DisplayMessage( "Current Directory: " . getcwd() );
		$this->DisplayMessage( "Changing Directory: " . $this->temporaryRoot );
		chdir( realpath( $this->temporaryRoot ) );

		$fullCommand = $command . implode( $commandParams, " " );

		$this->DisplayMessage( "Executing: " . $fullCommand );
		exit();

		/*
		sleep( 1 );
		$lastLine = system( $fullCommand );
		$successful = $lastLine !== false;
		*/

		$successful = $this->System( $fullCommand );

		chdir( $oldCwd );

		return $successful;
	}

	private function System( $command )
	{
		$fp = popen( $command . " 2>&1", "r" );

		while( !feof( $fp ) )
		{
			echo fread( $fp, 1024 );
		}

		fclose( $fp );

		return true;
	}

	public function CleanUp()
	{
		$this->DisplayMessage( "Temporary Root realpath: " . realpath( $this->temporaryRoot ) );
		$this->DisplayMessage( "Temporary Path realpath: " . realpath( $this->temporaryPath ) );

		FileSystem::Move( realpath( $this->temporaryPath ), $this->outputFolder );

		$this->DisplayMessage( "Output Folder realpath: " . realpath( $this->outputFolder ) );
	}

	private function SetFolder()
	{
		// remove the last / in the path
		$splitPath = explode( "/", $this->outputFolder, -1 );
		$outputFolder = array_pop( $splitPath );

		// not able to specify the actual output path
		// wget automatically saves within the host name folder
		$this->temporaryRoot = implode( "/", $splitPath );

		$splitPath[] = $_SERVER[ "HTTP_HOST" ];
		$this->temporaryPath = implode( "/", $splitPath );
	}

	private function DisplayMessage( $msg )
	{
		if( $this->verbose !== false )
		{
			echo "### " . $msg . "\n";
		}
	}
}