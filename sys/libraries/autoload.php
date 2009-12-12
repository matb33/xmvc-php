<?php

namespace xMVC;

class AutoLoad
{
	public static function ModelDriver( $className )
	{
		return( self::TryLoading( "drivers/" . AutoLoad::RemoveNamespaces( Normalize::Filename( $className ) ) . ".php" ) );
	}

	public static function Library( $className )
	{
		return( self::TryLoading( "libraries/" . AutoLoad::RemoveNamespaces( Normalize::Filename( $className ) ) . ".php" ) );
	}

	private static function RemoveNamespaces( $className )
	{
		return( substr( $className, strrpos( $className, "\\" ) ) );
	}

	private static function TryLoading( $file )
	{
		if( ( $classFile = Loader::Prioritize( $file ) ) !== false )
		{
			require_once( $classFile );

			return( true );
		}

		return( false );
	}
}

?>