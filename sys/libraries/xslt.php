<?php

class XSL
{
	public static function Transform( $xmlData, $xslData )
	{
		$processor = new XsltProcessor();

		libxml_use_internal_errors( true );

		$xml = new DomDocument();
		$xsl = new DomDocument();

		$xml->loadXML( $xmlData );
		$xsl->loadXML( $xslData );

		$processor->importStyleSheet( $xsl );

		$result = $processor->transformToXML( $xml );

		if( empty( $result ) )
		{
			trigger_error( self::DumpErrors( $processor ), E_USER_ERROR );
		}

		unset( $processor );

		return( $result );
	}

	private static function DumpErrors( $processor )
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
				$err .= "Line: " . $error->line . "<br/ >";
				$err .= "Column: " . $error->column . "<br/ >";

				if( $error->file )
				{
					$err .= "File: " . $error->file . "<br />";
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