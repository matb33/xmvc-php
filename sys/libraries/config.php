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
		$entry = null;
		$variablesToMerge = null;
		$variableToUnset = null;
		$configPath = $basePath . "/" . Loader::configFolder . "/";
		$handle = @dir( $configPath );

		if( $handle )
		{
			$existingVariables = get_defined_vars();

			while( ( $entry = $handle->read() ) !== false )
			{
				if( $entry != "." && $entry != ".." )
				{
					if( strtolower( substr( $entry, -4 ) ) == ( "." . Loader::configExtension ) )
					{
						include( $configPath . $entry );

						$variablesToMerge = array_diff_key( get_defined_vars(), $existingVariables, array( "existingVariables" => "" ) );
						self::$data = self::ArrayInsert( self::$data, $variablesToMerge );

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

	// This function was obtained from the comments on array_merge_recursive on php.net
	private static function ArrayInsert( $arr, $ins )
	{
		if( is_array( $arr ) && is_array( $ins ) )
		{
			foreach( $ins as $k => $v )
			{
				if( isset( $arr[ $k ] ) && is_array( $v ) && is_array( $arr[ $k ] ) )
				{
					$arr[ $k ] = self::ArrayInsert( $arr[ $k ], $v );
				}
				elseif( is_int( $k ) )
				{
					$arr[] = $v;
				}
				else
				{
					$arr[ $k ] = $v;
				}
			}
		}

		return( $arr );
	}
}

?>