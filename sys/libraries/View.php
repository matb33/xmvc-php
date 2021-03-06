<?php

namespace xMVC\Sys;

class View
{
	private $xmlData = null;
	private $xslData = null;
	private $xslViewName = null;
	private $xslViewFile = null;
	private $models = array();

	public function __construct( $xslViewName = null, $namespace = null )
	{
		if( ! is_null( $xslViewName ) )
		{
			$this->xslViewName = Loader::AssignDefaultNamespace( $xslViewName, $namespace );
		}
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

		return $model;
	}

	public function ShiftModels()
	{
		$model = array_shift( $this->models );

		return $model;
	}

	public function GetModels()
	{
		return $this->models;
	}

	public function PutModels( $models )
	{
		$this->models = $models;
	}

	public function RenderAsHTML( $data = null, $omitRoot = null )
	{
		return $this->Render( $data, "HTML", $omitRoot );
	}

	public function RenderAsXML( $data = null, $omitRoot = null )
	{
		return $this->Render( $data, "XML", $omitRoot );
	}

	public function Render( $data = null, $outputType = null, $omitRoot = null )
	{
		return $this->Load( $data, false, $outputType, $omitRoot );
	}

	public function ProcessAsHTML( $data = null, $omitRoot = null )
	{
		return $this->Process( $data, "HTML", $omitRoot );
	}

	public function ProcessAsXML( $data = null, $omitRoot = null )
	{
		return $this->Process( $data, "XML", $omitRoot );
	}

	public function Process( $data = null, $outputType = null, $omitRoot = null )
	{
		return $this->Load( $data, true, $outputType, $omitRoot );
	}

	public function Load( $data = null, $return = null, $outputType = null, $omitRoot = null )
	{
		$return = $this->GetReturn( $return );
		$outputType = $this->GetOutputType( $outputType );
		$omitRoot = $this->GetOmitRoot( $omitRoot );

		if( is_null( $this->GetXMLData() ) )
		{
			$this->SetXMLData( $this->StackModelsForView( $data, $this->GetOmitRoot( $omitRoot ) ) );
		}

		if( is_null( $this->GetXSLData() ) )
		{
			$this->SetXSLData( $this->ImportXSL( $data ) );
		}

		$result = null;

		if( ! is_null( $this->GetXSLData() ) && ! is_null( $this->GetXMLData() ) )
		{
			$result = $this->ProcessView( $return, $outputType );
		}
		else
		{
			trigger_error( "Could not find any XML data (model) and/or XSL data (view) while loading view [" . $this->xslViewName . "]", E_USER_ERROR );
		}

		return $result;
	}

	public function SetXSLData( $xslData )
	{
		$this->xslData = $xslData;
	}

	public function SetXMLData( $xmlData )
	{
		$this->xmlData = $xmlData;
	}

	public function GetXSLData()
	{
		return $this->xslData;
	}

	public function GetXMLData()
	{
		return $this->xmlData;
	}

	private function GetReturn( $return )
	{
		if( is_null( $return ) )
		{
			return false;
		}

		return $return;
	}

	private function GetOutputType( $outputType )
	{
		if( is_null( $outputType ) )
		{
			return "HTML";
		}

		return $outputType;
	}

	private function GetOmitRoot( $omitRoot )
	{
		if( is_null( $omitRoot ) )
		{
			return false;
		}

		return $omitRoot;
	}

	public function ImportXSL( $data = null, $xslViewFile = null )
	{
		$result = null;

		if( is_null( $xslViewFile ) )
		{
			$this->xslViewFile = Loader::Resolve( Loader::viewFolder, $this->xslViewName, Loader::viewExtension );
		}
		else
		{
			$this->xslViewFile = $xslViewFile;
		}

		if( file_exists( $this->xslViewFile ) )
		{
			if( Config::$data[ "enableInlinePHPInViews" ] )
			{
				$result = Loader::ParseExternal( $this->xslViewFile, $data );
			}
			else
			{
				$result = Loader::ReadExternal( $this->xslViewFile );
			}
		}
		else
		{
			if( $this->xslViewName != "" )
			{
				trigger_error( "XSL view name [" . $this->xslViewName . "] not found", E_USER_ERROR );
			}
			else
			{
				trigger_error( "XSL view file [" . $this->xslViewFile . "] not found", E_USER_ERROR );
			}
		}

		return $result;
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

		return $xmlHead . $xmlBody . $xmlFoot;
	}

	public function GetStackedModels()
	{
		$stack = "";

		if( is_array( $this->models ) )
		{
			foreach( $this->models as $model )
			{
				if( $model instanceof ModelDriver )
				{
					$stack .= $model->GetXMLForStacking();
				}
				else
				{
					trigger_error( "Invalid model was found in this view's model-stack", E_USER_ERROR );
				}
			}
		}

		return $stack;
	}

	public function ProcessView( $return, $outputType )
	{
		$result = null;

		if( self::ShouldRenderClientSide( $return ) )
		{
			OutputHeaders::XML();

			echo( $this->GetXMLData() );
		}
		else
		{
			$result = $this->Transform();

			if( ! $return )
			{
				OutputHeaders::Specifically( $outputType );

				echo( $result );
			}
		}

		return $result;
	}

	private function Transform()
	{
		$result = XSL::Transform( $this->GetXMLData(), $this->GetXSLData(), dirname( $this->xslViewFile ) );

		return $result;
	}

	private function ShouldRenderClientSide( $return )
	{
		if( $return === false )
		{
			if( Core::IsClientSideXSLTSupported() )
			{
				return true;
			}

			if( self::IsSourceViewOn() )
			{
				return true;
			}
		}

		return false;
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

		return $xmlResult;
	}

	public function GetXMLHead( $data, $omitRoot )
	{
		$encodedData = "";
		$sourceViewAttribute = "";
		$xmlHead = "";

		if( ! is_null( $data ) )
		{
			$encodedData = Normalize::EncodeData( $data );
		}

		if( self::IsSourceViewOn() )
		{
			$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"" . Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/xMVC::Sys::mcc\" ?" . ">\n";

			$sourceViewAttribute = " mcc=\"true\"";
		}
		else
		{
			if( Config::$data[ "enableInlinePHPInViews" ] )
			{
				if( ! is_null( $this->xslViewName ) )
				{
					$fullyQualifiedXsltViewName = str_replace( "\\", "::", Loader::AssignDefaultNamespace( $this->xslViewName ) );
					$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"" . Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/" . $fullyQualifiedXsltViewName . $encodedData . "\" ?" . ">\n";
				}
			}
		}

		if( ! $omitRoot )
		{
			$xmlHead .= "<xmvc:root xmlns:xmvc=\"" . Core::namespaceXML . "\"" . $sourceViewAttribute . ">\n";
		}

		return $xmlHead;
	}

	public function GetXMLFoot( $omitRoot )
	{
		$xmlFoot = "";

		if( ! $omitRoot )
		{
			$xmlFoot = "</xmvc:root>";
		}

		return $xmlFoot;
	}

	private function IsSourceViewOn()
	{
		return isset( $_GET[ Config::$data[ "sourceViewKey" ] ] ) && Config::$data[ "sourceViewEnabled" ];
	}
}