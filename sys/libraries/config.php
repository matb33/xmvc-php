<?php

namespace xMVC\Sys;

class Config
{
	public static $data = array();

	public static function Load( $path )
	{
		$path = substr( $path, -1 ) == "/" ? substr( $path, 0, -1 ) : $path;

		foreach( glob( $path ) as $expandedPath )
		{
			self::LoadByPath( $expandedPath );
		}
	}

	private static function LoadByPath( $basePath )
	{
		$variable = null;
		$value = null;
		$configFile = null;
		$variablesToMerge = null;
		$variableToUnset = null;

		$configPath = $basePath . "/" . Loader::configFolder . "/";
		$configFilePattern = $configPath . "*." . Loader::configExtension;

		$existingVariables = get_defined_vars();

		foreach( glob( $configFilePattern ) as $configFile )
		{
			include( $configFile );

			$variablesToMerge = array_diff_key( get_defined_vars(), $existingVariables, array( "existingVariables" => "" ) );
			self::$data = self::MergeVariables( self::$data, $variablesToMerge );

			foreach( array_keys( $variablesToMerge ) as $variableToUnset )
			{
				unset( $$variableToUnset );
			}
		}
	}

	private static function MergeVariables( $existingVariables, $variablesToMerge )
	{
		if( is_array( $existingVariables ) && is_array( $variablesToMerge ) )
		{
			foreach( $variablesToMerge as $k => $v )
			{
				if( isset( $existingVariables[ $k ] ) && is_array( $v ) && is_array( $existingVariables[ $k ] ) )
				{
					$existingVariables[ $k ] = self::MergeVariables( $existingVariables[ $k ], $v );
				}
				elseif( is_int( $k ) )
				{
					array_unshift( $existingVariables, $v );
				}
				else
				{
					$existingVariables[ $k ] = $v;
				}
			}
		}

		return( $existingVariables );
	}
}

?>