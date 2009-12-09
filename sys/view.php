<?php

// TO-DO: Refactor so that Process, Render, etc are even more abstracted so that there is a minimum of arguments passed to each

class View
{
	private $xmlData;
	private $xslData;
	private $models;

	public function __construct()
	{
		$this->xmlData	= null;
		$this->xslData	= null;
		$this->models	= array();
	}

	public function AddModel( $model )
	{
		$this->models[]	= $model;
	}

	public function PushModel( $model )
	{
		array_push( $this->models, $model );
	}

	public function UnShiftModel( $model )
	{
		array_unshift( $this->models, $model );
	}

	public function PopModels()
	{
		$model = array_pop( $this->models );

		return( $model );
	}

	public function ShiftModels()
	{
		$model = array_shift( $this->models );

		return( $model );
	}

	public function Load( $xslViewName = null, $data = null, $return = null, $outputType = null, $omitRoot = null )
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

	public function PrepareData( $xslViewName, $data, $omitRoot )
	{
		// If xslViewName is not specified, we assume that xslData and xmlData are already set from a previous call to Load, Render or Process.
		// This allows us to call Process on an view instance, and later on run a Render elsewhere without parameters.

		if( ! is_null( $xslViewName ) )
		{
			$this->xslData = $this->ImportXSL( $xslViewName, $data );

			if( ! is_null( $this->xslData ) )
			{
				$this->xmlData = $this->StackModelsForView( $xslViewName, $data, $omitRoot );
			}
		}
	}

	public function Render( $xslViewName = null, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, false, $outputType, $omitRoot ) );
	}

	public function Process( $xslViewName = null, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, true, $outputType, $omitRoot ) );
	}

	public function ImportXSL( $xslViewName, $data = null, $xslViewFile = null )
	{
		$result = null;

		if( is_null( $xslViewFile ) )
		{
			$xslViewFile = Loader::Prioritize( "views/" . $xslViewName . ".xsl" );
		}

		if( file_exists( $xslViewFile ) )
		{
			if( Config::$data[ "enableInlinePHPInViews" ] )
			{
				$result = Loader::ParseExternal( $xslViewFile, $data );
			}
			else
			{
				$result = Loader::ReadExternal( $xslViewFile, $data );
			}
		}
		else
		{
			trigger_error( "XSL view [" . $xslViewName . "] not found", E_USER_ERROR );
		}

		return( $result );
	}

	private function StackModelsForView( $xslViewName, $data, $omitRoot )
	{
		$result = null;
		$xmlBody = "";

		$xmlHead = $this->GetXMLHead( $xslViewName, $data, $omitRoot );
		$xmlFoot = $this->GetXMLFoot( $omitRoot );
		$xmlBody = $this->GetStackedModels();

		if( Config::$data[ "handleErrors" ] )
		{
			$xmlBody .= ErrorHandler::GetErrorsAsXML();
		}

		$xmlString = ( $xmlHead . $xmlBody . $xmlFoot );

		return( $xmlString );
	}

	private function ProcessView( $return, $outputType )
	{
		if( ( xMVC::IsClientSideXSLTSupported() || ( !xMVC::IsClientSideXSLTSupported() && isset( $_GET[ Config::$data[ "sourceViewKey" ] ] ) && Config::$data[ "sourceViewEnabled" ] ) ) && $return === false )
		{
			OutputHeaders::XML();

			echo( $this->xmlData );
		}
		else
		{
			$result = XSL::Transform( $this->xmlData, $this->xslData );

			if( ! $return )
			{
				OutputHeaders::Specifically( $outputType );

				echo( $result );
			}
		}

		return( $result );
	}

	public function PassThru( $data = null, $return = false, $omitRoot = true )
	{
		$xmlHead = "";
		$xmlFoot = "";
		$xmlBody = $this->GetStackedModels();

		if( $return )
		{
			$xmlString = $xmlBody;
		}
		else
		{
			$xmlHead = $this->GetXMLHead( "", $data, $omitRoot );
			$xmlFoot = $this->GetXMLFoot( $omitRoot );

			$xmlString = ( $xmlHead . $xmlBody . $xmlFoot );

			OutputHeaders::XML();

			echo( $xmlString );
		}

		return( $xmlString );
	}

	public function GetXMLHead( $xslViewName, $data, $omitRoot )
	{
		$encodedData = "";

		if( ! is_null( $data ) )
		{
			$encodedData = Loader::EncodeData( $data );
		}

		$xmlHead = "<" . "?xml version=\"1.0\" encoding=\"utf-8\" standalone=\"yes\" ?" . ">\n";

		if( isset( $_GET[ Config::$data[ "sourceViewKey" ] ] ) && Config::$data[ "sourceViewEnabled" ] )
		{
			$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"" . Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/mcc.xsl\" ?" . ">\n";

			if( ! $omitRoot )
			{
				$xmlHead .= "<xmvc:root xmlns:xmvc=\"" . xMVC::$namespace . "\" xmvc:mcc=\"true\">\n";
			}
		}
		else
		{
			if( $xslViewName != "" )
			{
				if( Config::$data[ "enableInlinePHPInViews" ] )
				{
					$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"" . Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/" . $xslViewName . $encodedData . "\" ?" . ">\n";
				}
				else
				{
					$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"app/views/" . $xslViewName . ".xsl\" ?" . ">\n";
				}
			}

			if( ! $omitRoot )
			{
				$xmlHead .= "<xmvc:root xmlns:xmvc=\"" . xMVC::$namespace . "\">\n";
			}
		}

		return( $xmlHead );
	}

	public function GetStackedModels()
	{
		$stack = "";

		if( is_array( $this->models ) )
		{
			foreach( $this->models as $model )
			{
				$driver = &$model->GetDriverInstance();
				$stack .= $driver->GetXMLForStacking();
			}
		}

		return( $stack );
	}

	public function GetXMLFoot( $omitRoot )
	{
		$xmlFoot = "";

		if( ! $omitRoot )
		{
			$xmlFoot = "</xmvc:root>";
		}

		return( $xmlFoot );
	}
}

?>