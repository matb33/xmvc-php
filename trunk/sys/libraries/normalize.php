<?php

namespace xMVC;

class Normalize
{
	public static function ObjectName( $name )
	{
		$name = str_replace( "-", "_", $name );
		$name = ucfirst( strtolower( preg_replace( "/ |\.|%20/", "", $name ) ) );

		return( $name );
	}

	public static function Filename( $name )
	{
		$name = self::RemoveNamespaces( strtolower( $name ) );

		return( $name );
	}

	private static function RemoveNamespaces( $className )
	{
		$lastBackSlashPosition = strrpos( $className, "\\" );

		if( $lastBackSlashPosition !== false )
		{
			$startPosition = $lastBackSlashPosition + 1;
		}
		else
		{
			$startPosition = 0;
		}

		return( substr( $className, $startPosition ) );
	}
}

?>