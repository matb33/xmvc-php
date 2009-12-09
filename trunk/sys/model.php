<?php

require( SYS_PATH . "driver.php" );

class Model
{
	private $driver = null;

	public function __construct( $driver = "xml" )
	{
		$success = null;

		$driverClassname = Normalize::ObjectName( $driver ) . "ModelDriver";

		if( ( $driverFile = Loader::Prioritize( "drivers/" . $driver . ".php" ) ) !== false )
		{
			require_once( $driverFile );

			$this->$driver	= new $driverClassname;
			$this->driver	= $driver;

			$success = true;
		}
		else
		{
			trigger_error( "Model driver [" . $driver . "] not found", E_USER_ERROR );
		}

		return( $success );
	}

	public function GetDriverInstance()
	{
		$driverName = $this->driver;

		return( $this->$driverName );
	}
}

?>