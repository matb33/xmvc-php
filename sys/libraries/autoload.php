<?php

namespace xMVC;

class AutoLoad
{
	public static function ModelDriver( $className )
	{
		return( self::TryLoading( "drivers", $className, "php" ) );
	}

	public static function Library( $className )
	{
		return( self::TryLoading( "libraries", $className, "php" ) );
	}

	private static function TryLoading( $folder, $file, $extension )
	{
		if( ( $classFile = Loader::Prioritize( $folder, $file, $extension ) ) !== false )
		{
			require_once( $classFile );

			return( true );
		}

		return( false );
	}
}

?>