<?php

require_once( SYS_PATH . "libraries/xslt.php" );

class View extends Root
{
	var $xmlData;
	var $xslData;

	var $models;

	function View()
	{
		parent::Root();

		$this->xmlData	= null;
		$this->xslData	= null;
		$this->models	= array();
	}

	function AddModel( $model )
	{
		$this->models[]	= $model;
	}

	function PushModel( $model )
	{
		array_push( $this->models, $model );
	}

	function UnShiftModel( $model )
	{
		array_unshift( $this->models, $model );
	}

	function PopModels()
	{
		$model = array_pop( $this->models );

		return( $model );
	}

	function ShiftModels()
	{
		$model = array_shift( $this->models );

		return( $model );
	}

	function Load( $xslViewName = null, $data = null, $return = null, $outputType = null, $omitRoot = null )
	{
		if( is_null( $return ) )
		{
			$return = false;
		}

		if( is_null( $outputType ) )
		{
			$outputType = "HTML";
		}

		if( is_null( $omitRoot ) )
		{
			$omitRoot = false;
		}

		$this->PrepareData( $xslViewName, $data, $omitRoot );

		if( ! is_null( $this->xslData ) && ! is_null( $this->xmlData ) )
		{
			$result = $this->ProcessView( $return, $outputType );
		}

		return( $result );
	}

	function PrepareData( $xslViewName, $data, $omitRoot )
	{
		// If not xslViewName is specified, we assume that xslData and xmlData are already set from a previous call to Load, Render or Process.
		// This allows us to call Process on an view instance, and later on run a Render elsewhere without parameters.

		if( ! is_null( $xslViewName ) )
		{
			$this->xslData = $this->ImportXSL( $xslViewName, $data );

			if( ! is_null( $this->xslData ) )
			{
				$this->xmlData = $this->ApplyViewToModels( $xslViewName, $data, $omitRoot );
			}
		}
	}

	function Render( $xslViewName = null, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, false, $outputType, $omitRoot ) );
	}

	function Process( $xslViewName = null, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, true, $outputType, $omitRoot ) );
	}

	function ImportXSL( $xslViewName, $data = null, $xslViewFile = null, $preservePHP = false )
	{
		$result = null;

		if( is_null( $xslViewFile ) )
		{
			$viewPaths = array();

			$viewPaths[ 0 ] = "views/" . $xslViewName . ".xsl";
			$viewPaths[ 1 ] = "views/" . $xslViewName . ".xsl.php";

			foreach( $viewPaths as $viewPath )
			{
				$applicationXslViewFile	= APP_PATH . $viewPath;
				$systemXslViewFile		= SYS_PATH . $viewPath;

				if( file_exists( $applicationXslViewFile ) )
				{
					$xslViewFile = $applicationXslViewFile;

					break;
				}
				else if( file_exists( $systemXslViewFile ) )
				{
					$xslViewFile = $systemXslViewFile;

					break;
				}
			}
		}

		if( file_exists( $xslViewFile ) )
		{
			if( $preservePHP )
			{
				$result = file_get_contents( $xslViewFile );
			}
			else
			{
				if( ! isset( $data[ "encodedData" ] ) )
				{
					$data[ "encodedData" ] = $this->EncodeData( $data );
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

				include( $xslViewFile );

				$result = ob_get_contents();

				ob_end_clean();
			}
		}
		else
		{
			trigger_error( "XSL view file '" . $xslViewFile . "' not found", E_USER_ERROR );
		}

		return( $result );
	}

	function ApplyViewToModels( $xslViewName, $data, $omitRoot )
	{
		$result = null;

		$xmlHead = "";
		$xmlBody = "";
		$xmlFoot = "";

		$xmlHead = $this->GetXMLHead( $xslViewName, $data, $omitRoot );
		$xmlFoot = $this->GetXMLFoot( $omitRoot );

		if( is_array( $this->models ) )
		{
			foreach( array_keys( $this->models ) as $key )
			{
				$model		= $this->models[ $key ];
				$driver		= &$model->GetDriverInstance();
				$xmlBody	.= ( $driver->GetXML( true ) );
			}
		}

		if( xMVC::HandleErrors() )
		{
			$xmlBody .= xMVC::ErrorHandler()->GetErrorsXML();
		}

		$xmlString = ( $xmlHead . $xmlBody . $xmlFoot );

		return( $xmlString );
	}

	function ProcessView( $return, $outputType )
	{
		if( ( xMVC::IsClientSideXSLTSupported() && $return === false ) || ( !xMVC::IsClientSideXSLTSupported() && isset( $_GET[ xMVC::SourceViewKey() ] ) && xMVC::SourceViewEnabled() ) )
		{
			xMVC::OutputXMLHeaders();

			echo( $this->xmlData );
		}
		else
		{
			$arguments = array(
				 "/_xml" => ( $this->xmlData ),
				 "/_xsl" => ( $this->xslData )
			);

			$parser = xslt_create();

			$result = xslt_process( $parser, "arg:/_xml", "arg:/_xsl", null, $arguments );

			if( empty( $result ) )
			{
				trigger_error( "Cannot process XSLT document [" . xslt_errno( $parser ) . "]: " . xslt_error( $parser ), E_USER_ERROR );
			}

			xslt_free( $parser );

			if( ! $return )
			{
				xMVC::OutputHeaders( $outputType );

				echo( $result );
			}
		}

		return( $result );
	}

	function PassThru( $data = null, $return = false, $omitRoot = true )
	{
		$xmlHead = "";
		$xmlBody = "";
		$xmlFoot = "";

		if( is_array( $this->models ) )
		{
			foreach( $this->models as $model )
			{
				$driver = &$model->GetDriverInstance();
				$xmlBody .= ( $driver->GetXML( true ) );
			}
		}

		if( $return )
		{
			$xmlString = $xmlBody;
		}
		else
		{
			$xmlHead = $this->GetXMLHead( "", $data, $omitRoot );
			$xmlFoot = $this->GetXMLFoot( $omitRoot );

			$xmlString = ( $xmlHead . $xmlBody . $xmlFoot );

			xMVC::OutputXMLHeaders();

			echo( $xmlString );
		}

		return( $xmlString );
	}

	function GetXMLHead( $xslViewName, $data, $omitRoot )
	{
		$encodedData = "";

		if( ! is_null( $data ) )
		{
			$encodedData = $this->EncodeData( $data );
		}

		$xmlHead = "<" . "?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?" . ">\n";

		$nameSpaces = "xmlns:xmvc=\"http://www.xmvc.org/ns/xmvc/1.0\"";

		if( isset( $_GET[ xMVC::SourceViewKey() ] ) && xMVC::SourceViewEnabled() )
		{
			$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"http://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/mcc.xsl" . $encodedData . "\" ?" . ">\n";

			if( ! $omitRoot )
			{
				$xmlHead .= "<xmvc:root " . trim( $nameSpaces ) . " xmvc:mcc=\"true\">\n";
			}
		}
		else
		{
			if( $xslViewName != "" )
			{
				$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"http://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/" . $xslViewName . $encodedData . "\" ?" . ">\n";
			}

			if( ! $omitRoot )
			{
				$xmlHead .= "<xmvc:root " . trim( $nameSpaces ) . ">\n";
			}
		}

		return( $xmlHead );
	}

	function GetXMLFoot( $omitRoot )
	{
		$xmlFoot = "";

		if( ! $omitRoot )
		{
			$xmlFoot = "</xmvc:root>";
		}

		return( $xmlFoot );
	}

	function EncodeData( $data )
	{
		// I think we need to have a fix here if the length of data is greater than 256 chars, URL's wont handle too large. Maybe use sessions. --Yvon
		return( "/_enc_" . str_replace( "=", "_", base64_encode( serialize( $data ) ) ) );
	}
}

?>