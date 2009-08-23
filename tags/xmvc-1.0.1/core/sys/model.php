<?php

class Model extends Root
{
	var $driver = null;

	function Model( $driver = "xml" )
	{
		parent::Root();

		$success = null;

		$driverClassname		= xMVC::NormalizeName( $driver ) . "ModelDriver";

		$applicationDriverFile	= APP_PATH . "models/drivers/" . $driver . ".php";
		$systemDriverFile		= SYS_PATH . "models/drivers/" . $driver . ".php";

		if( file_exists( $applicationDriverFile ) )
		{
			$driverFile = $applicationDriverFile;
		}
		else
		{
			$driverFile = $systemDriverFile;
		}

		if( file_exists( $driverFile ) )
		{
			require_once( $driverFile );

			$this->$driver	= new $driverClassname;
			$this->driver	= $driver;

			$success = true;
		}
		else
		{
			trigger_error( "Model driver file '" . $driverFile . "' not found", E_USER_ERROR );
		}

		return( $success );
	}

	function GetDriverInstance()
	{
		$driver = $this->driver;

		return( $this->$driver );
	}
}

?>