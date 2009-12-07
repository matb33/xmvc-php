<?php

class Config
{
	public static $data = array();

	public function Load( $basePath )
	{
		$entry = null;
		$configPath = $basePath . "config/";
		$handle = dir( $configPath );

		$existingVariables = get_defined_vars();

		while( ( $entry = $handle->read() ) !== false )
		{
			if( $entry != "." && $entry != ".." )
			{
				if( strtolower( substr( $entry, -4 ) ) == ".php" )
				{
					include( $configPath . $entry );

					foreach( array_diff_key( get_defined_vars(), $existingVariables, array( "existingVariables" => "" ) ) as $variable => $value )
					{
						self::$data[ $variable ] = $value;
					}
				}
			}
		}

		$handle->close();
	}
}

?>