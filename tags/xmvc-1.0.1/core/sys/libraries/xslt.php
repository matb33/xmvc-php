<?php

if( version_compare( PHP_VERSION, "5.1.2", ">=" ) )
{
	function xslt_create()
	{
		return( new XsltProcessor() );
	}

	function xslt_process( $xsltproc, $xml_arg, $xsl_arg, $xslcontainer = null, $args = null, $params = null )
	{
		libxml_use_internal_errors( true );

		// Start with preparing the arguments

		$xml_arg = str_replace( "arg:", "", $xml_arg );
		$xsl_arg = str_replace( "arg:", "", $xsl_arg );

		// Create instances of the DomDocument class

		$xml = new DomDocument;
		$xsl = new DomDocument;

		// Load the xml document and the xsl template

		$xml->loadXML( $args[ $xml_arg ] );
		$xsl->loadXML( $args[ $xsl_arg ] );

		// Load the xsl template

		$xsltproc->importStyleSheet( $xsl );

		// Set parameters when defined

		if( $params )
		{
			foreach( $params as $param => $value )
			{
				$xsltproc->setParameter( "", $param, $value );
			}
		}

		// Start the transformation

		$processed = $xsltproc->transformToXML( $xml );

		// Put the result in a file when specified

		if( $xslcontainer )
		{
			return( file_put_contents( $xslcontainer, $processed ) );
		}
		else
		{
			return( $processed );
		}
	}

	function xslt_set_base( $xsltproc, $base )
	{
	}

	function xslt_free( $xsltproc )
	{
		unset( $xsltproc );
	}

	function xslt_errno( $xsltproc )
	{
		return( "N/A" );
	}

	function xslt_error( $xsltproc )
	{
		// TO-DO: Clean this up

		$err = "";
		$newline = "<br />";
		
		$errors = libxml_get_errors();

		if( ! empty( $errors ) )
		{
			foreach( $errors as $error )
			{
				//$err = "";
				
				switch( $error->level )
				{
					case LIBXML_ERR_WARNING:
						$err .= $newline . "<strong>Warning " . $error->code . "</strong>: ";
					break;

					case LIBXML_ERR_ERROR:
						$err .= $newline . "<strong>Error " . $error->code . "</strong>: ";
					break;

					case LIBXML_ERR_FATAL:
						$err .= $newline . "<strong>Fatal Error " . $error->code . "</strong>: ";
					break;
				}

				$err .= trim( $error->message );
				$err .= $newline . "Line: " . $error->line;
				$err .= $newline . "Column: " . $error->column;

				if( $error->file )
				{
					$err .= $newline . "File: " . $error->file . "<hr />";
				}
				
				$err .=  "<hr />";
			}
		}

		echo "<h1>xslt_error</h1><pre>";
		print_r( $err );
		echo "</pre>";

		return( $err );
	}
}
?>