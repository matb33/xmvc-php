<?php

namespace xMVC;

class Loader
{
	public static function Prioritize( $folder, $file, $extension )
	{
		$file = Normalize::Filename( $file );
		$path = self::FindPathWhereFileExists( $folder, $file, $extension );

		if( $path !== false )
		{
			return( $path . $folder . "/" . $file . "." . $extension );
		}

		return( false );
	}

	public static function FindPathWhereFileExists( $folder, &$file, $extension )
	{
		if( ( $path = self::FindByApp( $folder, $file, $extension ) ) !== false )
		{
			return( $path );
		}

		if( ( $path = self::FindByMod( $folder, $file, $extension ) ) !== false )
		{
			return( $path );
		}

		if( ( $path = self::FindBySys( $folder, $file, $extension ) ) !== false )
		{
			return( $path );
		}

		return( false );
	}

	private static function FindByApp( $folder, &$file, $extension )
	{
		$appFile = APP_PATH . $folder . "/" . $file . "." . $extension;

		if( file_exists( $appFile ) )
		{
			return( APP_PATH );
		}

		return( false );
	}

	private static function FindByMod( $folder, &$file, $extension )
	{
		$moduleNamespace = self::ExtractModuleNamespace( $file );

		if( $moduleNamespace !== false )
		{
			$modFile = MOD_PATH . $moduleNamespace . "/" . $folder . "/" . $file . "." . $extension;

			if( file_exists( $modFile ) )
			{
				return( MOD_PATH . $moduleNamespace . "/" );
			}
		}

		return( false );
	}

	private static function FindBySys( $folder, &$file, $extension )
	{
		$sysFile = SYS_PATH . $folder . "/" . $file . "." . $extension;

		if( file_exists( $sysFile ) )
		{
			return( SYS_PATH );
		}

		return( false );
	}

	private static function ExtractModuleNamespace( &$file )
	{
		$parts = explode( "\\", $file );

		if( count( $parts ) > 1 )
		{
			$namespace = self::GetFirstPart( $parts );
			$file = self::GetRemainingParts( $parts );

			return( $namespace );
		}

		return( false );
	}

	private static function GetFirstPart( $parts )
	{
		return( $parts[ 0 ] );
	}

	private static function GetRemainingParts( $parts )
	{
		return( implode( "/", array_slice( $parts, 1 ) ) );
	}

	public static function ReadExternal( $filename )
	{
		return( file_get_contents( $filename ) );
	}

	public static function ParseExternal( $filename, $data )
	{
		if( ! isset( $data[ "encodedData" ] ) )
		{
			$data[ "encodedData" ] = self::EncodeData( $data );
		}

		if( ! is_null( $data ) && is_array( $data ) )
		{
			extract( $data, EXTR_SKIP );
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