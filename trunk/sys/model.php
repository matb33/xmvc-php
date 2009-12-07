<?php

require( SYS_PATH . "driver.php" );

class Model
{
	private $driver = null;

	public function __construct( $driver = "xml" )
	{
		$success = null;

		$driverClassname = Normalize::ObjectName( $driver ) . "ModelDriver";
		$driverFile	= Loader::Prioritize( "models/drivers/" . $driver . ".php" );

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

	public function GetDriverInstance()
	{
		$driver = $this->driver;

		return( $this->$driver );
	}
}

?>