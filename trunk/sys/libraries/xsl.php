<?php

namespace xMVC\Sys;

class XSL
{
	private static $processor;

	public static function Transform( $xmlData, $xslData )
	{
		self::$processor = new \XSLTProcessor();

		libxml_use_internal_errors( true );

		self::SetupPHPFunctions();
		self::SetupProfiling();

		$xml = new \DOMDocument( "1.0", "UTF-8" );
		$xsl = new \DOMDocument( "1.0", "UTF-8" );

		$xml->loadXML( $xmlData );
		$xsl->loadXML( $xslData );

		self::$processor->importStyleSheet( $xsl );

		$result = self::$processor->transformToXML( $xml );

		if( empty( $result ) )
		{
			trigger_error( self::DumpErrors(), E_USER_ERROR );
		}

		self::$processor = null;

		return( $result );
	}

	private static function SetupPHPFunctions()
	{
		if( isset( Config::$data[ "enableXSLTPHPFunctions" ] ) )
		{
			if( Config::$data[ "enableXSLTPHPFunctions" ] )
			{
				$restrict = array();

				if( isset( Config::$data[ "restrictXSLTPHPFunctions" ] ) )
				{
					$restrict = Config::$data[ "restrictXSLTPHPFunctions" ];
				}

				self::$processor->registerPHPFunctions( $restrict );
			}
		}
	}

	private static function SetupProfiling()
	{
		if( isset( Config::$data[ "enableXSLTProfiling" ] ) )
		{
			if( Config::$data[ "enableXSLTProfiling" ] )
			{
				if( isset( Config::$data[ "XSLTProfilingFilename" ] ) )
				{
					if( strlen( trim( Config::$data[ "XSLTProfilingFilename" ] ) ) > 0 )
					{
						self::$processor->setProfiling( Config::$data[ "XSLTProfilingFilename" ] );
					}
				}
			}
		}
	}

	private static function DumpErrors()
	{
		// TO-DO: Clean this up

		$err = "";

		$errors = libxml_get_errors();

		if( ! empty( $errors ) )
		{
			foreach( $errors as $error )
			{
				switch( $error->level )
				{
					case LIBXML_ERR_WARNING:
						$err .= "<strong>Warning [" . $error->code . "]</strong>: ";
					break;

					case LIBXML_ERR_ERROR:
						$err .= "<strong>Error [" . $error->code . "]</strong>: ";
					break;

					case LIBXML_ERR_FATAL:
						$err .= "<strong>Fatal Error [" . $error->code . "]</strong>: ";
					break;

					default:
						$err .= "<strong>Unknown Error [" . $error->code . "]</strong>: ";
				}

				$err .= trim( $error->message ) . "<br/ >";
				$err .= "Line: [" . $error->line . "]<br/ >";
				$err .= "Column: [" . $error->column . "]<br/ >";

				if( $error->file )
				{
					$err .= "File: [" . $error->file . "]<br />";
				}

				$err .=  "<hr />";
			}
		}

		echo "<h1>XSL::DumpErrors</h1><pre>";
		print_r( $err );
		echo "</pre>";

		return( $err );
	}
}

?>