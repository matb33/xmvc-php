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
		$name = strtolower( $name );

		return( $name );
	}
}

?>