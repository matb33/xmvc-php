<?php

class Loader
{
	public static function Prioritize( $filename )
	{
		$applicationFilename = APP_PATH . $filename;
		$systemFilename = SYS_PATH . $filename;

		if( file_exists( $applicationFilename ) )
		{
			return( $applicationFilename );
		}
		else if( file_exists( $systemFilename ) )
		{
			return( $systemFilename );
		}
		else
		{
			return( false );
		}
	}

	public static function ReadExternal( $filename, $data )
	{
		return( file_get_contents( $filename ) );
	}

	public static function ParseExternal( $filename, $data )
	{
		if( ! isset( $data[ "encodedData" ] ) )
		{
			$data[ "encodedData" ] = self::EncodeData( $data );
		}

		if( is_array( $data ) )
		{
			// Bring variables from data array into local scope

			foreach( $data as $key => $value )
			{
				eval( "\$$key = \$value;" );
			}
		}

		ob_start();

		include( $filename );

		$result = ob_get_contents();

		ob_end_clean();

		return( $result );
	}

	public static function EncodeData( $data )
	{
		return( "/_enc_" . str_replace( "=", "_", base64_encode( serialize( $data ) ) ) );
	}
}

?>