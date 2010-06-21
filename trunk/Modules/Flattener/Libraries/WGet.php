<?php

namespace Modules\Flattener\Libraries;

use System\Libraries\Config;
use System\Libraries\FileSystem;

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
		{
			$this->properties = $properties;
		}

		$this->outputFolder = $outputFolder;
		$this->verbose = $verbose;
	}

	public function execute( $url, $properties = array(), $verbose = true )
	{
		$this->verbose = $verbose;

		$commandParams = array();

		// overwrite properties with passed-in values
		if( !is_null( $properties ) )
		{
			$properties = array_merge( $this->properties,  $properties );
		}

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

		$this->setFolder();

		$command = realpath( Config::$data[ "wgetExecutable" ] ) . " ";
		$command = str_replace( "\\", "/", $command );

		$oldCwd = getcwd();
		$this->displayMessage( "Current Directory: " . getcwd() );
		$this->displayMessage( "Changing Directory: " . $this->temporaryRoot );
		chdir( realpath( $this->temporaryRoot ) );

		$this->displayMessage( "Executing: " . $command . implode( $commandParams, " " ) );
		exec( $command . implode( $commandParams, " " ), $results, $returnStatus );

		$this->displayMessage( implode( $results, "<br />\n" ) );
		$this->displayMessage( "Return Status: " . $returnStatus );

		chdir( $oldCwd );
	}

	public function CleanUp()
	{
		$this->displayMessage( "Temporary Root realpath: " . realpath( $this->temporaryRoot ) );
		$this->displayMessage( "Temporary Path realpath: " . realpath( $this->temporaryPath ) );

		FileSystem::move( realpath( $this->temporaryPath ), $this->outputFolder );

		$this->displayMessage( "Output Folder realpath: " . realpath( $this->outputFolder ) );
	}

	private function setFolder()
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

	private function displayMessage( $msg )
	{
		if( $this->verbose !== false )
		{
			echo( $msg . "<br />\n" );
		}
	}
}