<?php

namespace xMVC;

class AutoLoad
{
	public static function ModelDriver( $className )
	{
		return( self::TryLoading( "drivers", Normalize::Filename( $className ), "php" ) );
	}

	public static function Library( $className )
	{
		return( self::TryLoading( "libraries", Normalize::Filename( $className ), "php" ) );
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