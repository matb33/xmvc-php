<?php

require_once( SYS_PATH . "libraries/xslt.php" );

class Config
{
	function Load( $basePath )
	{
		$xmlConfigFile	= $basePath . "config/config.xml";
		$xslConfigFile	= SYS_PATH . "config/config.xsl";

		if( file_exists( $xmlConfigFile ) )
		{
			if( file_exists( $xslConfigFile ) )
			{
				$arguments = array(
					 "/_xml" => ( file_get_contents( $xmlConfigFile ) ),
					 "/_xsl" => ( file_get_contents( $xslConfigFile ) )
				);

				$parser = xslt_create();

				$result = xslt_process( $parser, "arg:/_xml", "arg:/_xsl", null, $arguments );

				if( empty( $result ) )
				{
					trigger_error( "Cannot process XSLT document [" . xslt_errno( $parser ) . "]: " . xslt_error( $parser ), E_USER_ERROR );
				}

				xslt_free( $parser );

				Config::__Parse( $result );
			}
			else
			{
				trigger_error( "XSL config file '" . $xslConfigFile . "' not found", E_USER_ERROR );
			}
		}
		else
		{
			trigger_error( "XML config file '" . $xmlConfigFile . "' not found", E_USER_ERROR );
		}
	}

	function __Parse( $result )
	{
		eval( $result );

		foreach( $config as $key => $value )
		{
			Config::Value( $key, $value );
		}
	}

	function Value( $key, $value = null )
	{
		static $storedValue;

		if( ! is_null( $value ) )
		{
			$storedValue[ $key ] = $value;
		}

		return( $storedValue[ $key ] );
	}
}

?>