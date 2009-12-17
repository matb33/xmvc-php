<?php

namespace xMVC;

class AutoLoad
{
	public static function ModelDriver( $className )
	{
		return( self::TryLoading( Core::$driverFolder, $className, Core::$driverExtension ) );
	}

	public static function Library( $className )
	{
		return( self::TryLoading( Core::$libraryFolder, $className, Core::$libraryExtension ) );
	}

	public static function Controller( $className )
	{
		return( self::TryLoading( Core::$controllerFolder, $className, Core::$controllerExtension ) );
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