<?php

function __autoload( $className )
{
	if( ! AutoLoad::ModelDriver( $className ) )
	{
		if( ! AutoLoad::Library( $className ) )
		{
			trigger_error( "Unable to load a model-driver or library by the name [" . $className . "]", E_USER_ERROR );
		}
	}
}

class AutoLoad
{
	public static function ModelDriver( $className )
	{
		return( self::TryLoading( "drivers/" . Normalize::Filename( $className ) . ".php" ) );
	}

	public static function Library( $className )
	{
		return( self::TryLoading( "libraries/" . Normalize::Filename( $className ) . ".php" ) );
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