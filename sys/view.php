<?php

class View
{
	private $xmlData = null;
	private $xslData = null;
	private $models = array();

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

	public function RenderAsHTML( $xslViewName, $data = null, $omitRoot = null )
	{
		return( $this->Render( $xslViewName, $data, "HTML", $omitRoot ) );
	}

	public function RenderAsXML( $xslViewName, $data = null, $omitRoot = null )
	{
		return( $this->Render( $xslViewName, $data, "XML", $omitRoot ) );
	}

	public function Render( $xslViewName, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, false, $outputType, $omitRoot ) );
	}

	public function ProcessAsHTML( $xslViewName, $data = null, $omitRoot = null )
	{
		return( $this->Process( $xslViewName, $data, "HTML", $omitRoot ) );
	}

	public function ProcessAsXML( $xslViewName, $data = null, $omitRoot = null )
	{
		return( $this->Process( $xslViewName, $data, "XML", $omitRoot ) );
	}

	public function Process( $xslViewName, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, true, $outputType, $omitRoot ) );
	}

	public function Load( $xslViewName, $data = null, $return = null, $outputType = null, $omitRoot = null )
	{
		$return = $this->GetReturn( $return );
		$outputType = $this->GetOutputType( $outputType );
		$omitRoot = $this->GetOmitRoot( $omitRoot );

		$this->PrepareData( $xslViewName, $data, $omitRoot );

		if( ! is_null( $this->xslData ) && ! is_null( $this->xmlData ) )
		{
			$result = $this->ProcessView( $return, $outputType );
		}
		else
		{
			trigger_error( "Could not find any XML data (model) and/or XSL data (view) while loading view [" . $xslViewName . "]", E_USER_ERROR );
		}

		return( $result );
	}

	public function PrepareData( $xslViewName = null, $data = null, $omitRoot = null )
	{
		// If xslViewName is not null, we assume that xslData and xmlData are already set from a previous call to Load, Render or Process.
		// This allows us to call Process on an view instance, and later on run a Render elsewhere without parameters.

		if( ! is_null( $xslViewName ) )
		{
			$this->xslData = $this->ImportXSL( $xslViewName, $data );

			if( ! is_null( $this->xslData ) )
			{
				$omitRoot = $this->GetOmitRoot( $omitRoot );

				$this->xmlData = $this->StackModelsForView( $xslViewName, $data, $omitRoot );
			}
		}
	}

	private function GetReturn( $return )
	{
		if( is_null( $return ) )
		{
			return( false );
		}

		return( $return );
	}

	private function GetOutputType( $outputType )
	{
		if( is_null( $outputType ) )
		{
			return( "HTML" );
		}

		return( $outputType );
	}

	private function GetOmitRoot( $omitRoot )
	{
		if( is_null( $omitRoot ) )
		{
			return( false );
		}

		return( $omitRoot );
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
		$xmlHead = $this->GetXMLHead( $xslViewName, $data, $omitRoot );
		$xmlBody = $this->GetStackedModels();
		$xmlFoot = $this->GetXMLFoot( $omitRoot );

		if( Config::$data[ "handleErrors" ] )
		{
			$xmlBody .= ErrorHandler::GetErrorsAsXML();
		}

		return( $xmlHead . $xmlBody . $xmlFoot );
	}

	public function GetStackedModels()
	{
		$stack = "";

		if( is_array( $this->models ) )
		{
			foreach( $this->models as $model )
			{
				$stack .= $model->GetXMLForStacking();
			}
		}

		return( $stack );
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
		$xmlBody = $this->GetStackedModels();

		if( $return )
		{
			$xmlResult = $xmlBody;
		}
		else
		{
			$xmlHead = $this->GetXMLHead( "", $data, $omitRoot );
			$xmlFoot = $this->GetXMLFoot( $omitRoot );

			$xmlResult = ( $xmlHead . $xmlBody . $xmlFoot );

			OutputHeaders::XML();

			echo( $xmlResult );
		}

		return( $xmlResult );
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