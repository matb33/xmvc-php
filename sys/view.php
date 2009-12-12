<?php

namespace xMVC;

class View
{
	private $xmlData = null;
	private $xslData = null;
	private $xslViewName = null;
	private $models = array();

	public function __construct( $xslViewName )
	{
		$this->xslViewName = $xslViewName;
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

	public function RenderAsHTML( $data = null, $omitRoot = null )
	{
		return( $this->Render( $data, "HTML", $omitRoot ) );
	}

	public function RenderAsXML( $data = null, $omitRoot = null )
	{
		return( $this->Render( $data, "XML", $omitRoot ) );
	}

	public function Render( $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $data, false, $outputType, $omitRoot ) );
	}

	public function ProcessAsHTML( $data = null, $omitRoot = null )
	{
		return( $this->Process( $data, "HTML", $omitRoot ) );
	}

	public function ProcessAsXML( $data = null, $omitRoot = null )
	{
		return( $this->Process( $data, "XML", $omitRoot ) );
	}

	public function Process( $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $data, true, $outputType, $omitRoot ) );
	}

	public function Load( $data = null, $return = null, $outputType = null, $omitRoot = null )
	{
		$return = $this->GetReturn( $return );
		$outputType = $this->GetOutputType( $outputType );
		$omitRoot = $this->GetOmitRoot( $omitRoot );

		if( is_null( $this->xmlData ) )
		{
			$this->PrepareData( $data, $omitRoot );
		}

		if( ! is_null( $this->xslData ) && ! is_null( $this->xmlData ) )
		{
			$result = $this->ProcessView( $return, $outputType );
		}
		else
		{
			trigger_error( "Could not find any XML data (model) and/or XSL data (view) while loading view [" . $this->xslViewName . "]", E_USER_ERROR );
		}

		return( $result );
	}

	private function PrepareData( $data = null, $omitRoot = null )
	{
		$this->xslData = $this->ImportXSL( $data );
		$this->xmlData = $this->StackModelsForView( $data, $this->GetOmitRoot( $omitRoot ) );
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

	public function ImportXSL( $data = null, $xslViewFile = null )
	{
		$result = null;

		if( is_null( $xslViewFile ) )
		{
			$xslViewFile = Loader::Prioritize( "views/" . $this->xslViewName . ".xsl" );
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
			trigger_error( "XSL view [" . $this->xslViewName . "] not found", E_USER_ERROR );
		}

		return( $result );
	}

	private function StackModelsForView( $data, $omitRoot )
	{
		$xmlHead = $this->GetXMLHead( $data, $omitRoot );
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
		if( ( Core::IsClientSideXSLTSupported() || ( !Core::IsClientSideXSLTSupported() && isset( $_GET[ Config::$data[ "sourceViewKey" ] ] ) && Config::$data[ "sourceViewEnabled" ] ) ) && $return === false )
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
			$xmlHead = $this->GetXMLHead( $data, $omitRoot );
			$xmlFoot = $this->GetXMLFoot( $omitRoot );

			$xmlResult = ( $xmlHead . $xmlBody . $xmlFoot );

			OutputHeaders::XML();

			echo( $xmlResult );
		}

		return( $xmlResult );
	}

	public function GetXMLHead( $data, $omitRoot )
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
				$xmlHead .= "<xmvc:root xmlns:xmvc=\"" . Core::$namespace . "\" xmvc:mcc=\"true\">\n";
			}
		}
		else
		{
			if( $this->xslViewName != "" )
			{
				if( Config::$data[ "enableInlinePHPInViews" ] )
				{
					$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"" . Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/" . $this->xslViewName . $encodedData . "\" ?" . ">\n";
				}
				else
				{
					$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"app/views/" . $this->xslViewName . ".xsl\" ?" . ">\n";
				}
			}

			if( ! $omitRoot )
			{
				$xmlHead .= "<xmvc:root xmlns:xmvc=\"" . Core::$namespace . "\">\n";
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