<?php

namespace System\Libraries;

class Config
{
	public static $data = array();

	public static function Load()
	{
		// $t1 = microtime( true );		// With a moderate amount of modules, loading takes about 0.02 seconds. Though should be cached, it's a low priority

		$paths = func_get_args();
		$aggregatedPaths = array();

		foreach( $paths as $path )
		{
			$path = substr( $path, -1 ) == "/" ? substr( $path, 0, -1 ) : $path;
			$expandedPaths = glob( $path, GLOB_ONLYDIR | GLOB_BRACE );
			$aggregatedPaths = array_merge( $aggregatedPaths, $expandedPaths );
		}

		$aggregatedPaths = array_unique( $aggregatedPaths );

		foreach( $aggregatedPaths as $expandedPath )
		{
			self::LoadByPath( $expandedPath );
		}

		// var_dump( microtime( true ) - $t1 );
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
		$configFiles = glob( $configFilePattern );

		foreach( $configFiles as $configFile )
		{
			include $configFile;

			$variablesToMerge = array_diff_key( get_defined_vars(), $existingVariables, array( "existingVariables" => "", "configFiles" => "", "variablesToMergeKeys" => "" ) );
			self::$data = self::MergeVariables( self::$data, $variablesToMerge );
			$variablesToMergeKeys = array_keys( $variablesToMerge );

			foreach( $variablesToMergeKeys as $variableToUnset )
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

		return $existingVariables;
	}
}