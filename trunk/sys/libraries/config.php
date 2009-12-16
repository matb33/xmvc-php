<?php

namespace xMVC;

class Config
{
	public static $data = array();

	public static function Load()
	{
		self::LoadByPath( SYS_PATH );
		self::LoadByPath( APP_PATH );
	}

	public static function LoadByPath( $basePath )
	{
		$variable = null;
		$value = null;
		$entry = null;
		$variablesToMerge = null;
		$variableToUnset = null;
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

					$variablesToMerge = array_diff_key( get_defined_vars(), $existingVariables, array( "existingVariables" => "" ) );
					self::$data = array_merge_recursive( self::$data, $variablesToMerge );

					foreach( array_keys( $variablesToMerge ) as $variableToUnset )
					{
						unset( $$variableToUnset );
					}
				}
			}
		}

		$handle->close();
	}
}

?>