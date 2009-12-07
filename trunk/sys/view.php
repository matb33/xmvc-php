<?php

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

	private function PrepareData( $xslViewName, $data, $omitRoot )
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

	public function Render( $xslViewName = null, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, false, $outputType, $omitRoot ) );
	}

	public function Process( $xslViewName = null, $data = null, $outputType = null, $omitRoot = null )
	{
		return( $this->Load( $xslViewName, $data, true, $outputType, $omitRoot ) );
	}

	public function ImportXSL( $xslViewName, $data = null, $xslViewFile = null, $preservePHP = false )
	{
		$result = null;

		if( is_null( $xslViewFile ) )
		{
			$viewPaths = array();

			$viewPaths[ 0 ] = "views/" . $xslViewName . ".xsl";
			$viewPaths[ 1 ] = "views/" . $xslViewName . ".xsl.php";

			foreach( $viewPaths as $viewPath )
			{
				$xslViewFile = Loader::Prioritize( $viewPath );

				if( ! is_null( $xslViewFile ) )
				{
					break;
				}
			}
		}

		if( file_exists( $xslViewFile ) )
		{
			if( $preservePHP )
			{
				$result = Loader::ReadExternal( $xslViewFile, $data );
			}
			else
			{
				$result = Loader::ParseExternal( $xslViewFile, $data );
			}
		}
		else
		{
			trigger_error( "XSL view file '" . $xslViewFile . "' not found", E_USER_ERROR );
		}

		return( $result );
	}

	private function ApplyViewToModels( $xslViewName, $data, $omitRoot )
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

		$nameSpaces = "xmlns:xmvc=\"" . xMVC::$namespace . "\"";

		if( isset( $_GET[ Config::$data[ "sourceViewKey" ] ] ) && Config::$data[ "sourceViewEnabled" ] )
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