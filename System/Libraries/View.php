<?php

namespace System\Libraries;

class View
{
	const namespaceXML = "http://www.xmvc.org/ns/xmvc/1.0";

	private $xmlData = null;
	private $xslData = null;
	private $xslViewName = null;
	private $xslViewFile = null;
	private $models = array();

	public function __construct( $xslViewName = null, $namespace = null )
	{
		if( ! is_null( $xslViewName ) )
		{
			$this->xslViewName = Loader::assignDefaultNamespace( $xslViewName, $namespace, Loader::viewFolder );
		}
	}

	public function addModel( $model )
	{
		$this->models[]	= $model;
	}

	public function pushModel( $model )
	{
		array_push( $this->models, $model );
	}

	public function unShiftModel( $model )
	{
		array_unshift( $this->models, $model );
	}

	public function popModels()
	{
		$model = array_pop( $this->models );

		return $model;
	}

	public function shiftModels()
	{
		$model = array_shift( $this->models );

		return $model;
	}

	public function getModels()
	{
		return $this->models;
	}

	public function putModels( $models )
	{
		$this->models = $models;
	}

	public function renderAsHTML( $data = null, $omitRoot = null, $cacheTime = null )
	{
		return $this->render( $data, "HTML", $omitRoot, $cacheTime );
	}

	public function renderAsXML( $data = null, $omitRoot = null, $cacheTime = null )
	{
		return $this->render( $data, "XML", $omitRoot, $cacheTime );
	}

	public function render( $data = null, $outputType = null, $omitRoot = null, $cacheTime = null )
	{
		return $this->load( $data, false, $outputType, $omitRoot, $cacheTime );
	}

	public function processAsHTML( $data = null, $omitRoot = null, $cacheTime = null )
	{
		return $this->process( $data, "HTML", $omitRoot, $cacheTime );
	}

	public function processAsXML( $data = null, $omitRoot = null, $cacheTime = null )
	{
		return $this->process( $data, "XML", $omitRoot, $cacheTime );
	}

	public function process( $data = null, $outputType = null, $omitRoot = null, $cacheTime = null )
	{
		return $this->load( $data, true, $outputType, $omitRoot, $cacheTime );
	}

	public function load( $data = null, $return = null, $outputType = null, $omitRoot = null, $cacheTime = null )
	{
		$return = $this->getReturn( $return );
		$outputType = $this->getOutputType( $outputType );
		$omitRoot = $this->getOmitRoot( $omitRoot );
		$cacheTime = $this->getCacheTime( $cacheTime );

		if( is_null( $this->getXMLData() ) )
		{
			$this->setXMLData( $this->aggregateModelsForView( $data, $this->getOmitRoot( $omitRoot ) ) );
		}

		if( is_null( $this->getXSLData() ) )
		{
			$this->setXSLData( $this->importXSL( $data ) );
		}

		$result = null;

		if( ! is_null( $this->getXSLData() ) && ! is_null( $this->getXMLData() ) )
		{
			$result = $this->processView( $return, $outputType, $cacheTime );
		}
		else
		{
			trigger_error( "Could not find any XML data (model) and/or XSLT data (view) while loading view [" . $this->xslViewName . "]", E_USER_ERROR );
		}

		return $result;
	}

	public function setXSLData( $xslData )
	{
		$this->xslData = $xslData;
	}

	public function setXMLData( $xmlData )
	{
		$this->xmlData = $xmlData;
	}

	public function getXSLData()
	{
		return $this->xslData;
	}

	public function getXMLData()
	{
		return $this->xmlData;
	}

	private function getReturn( $return )
	{
		if( is_null( $return ) )
		{
			return false;
		}

		return $return;
	}

	private function getOutputType( $outputType )
	{
		if( is_null( $outputType ) )
		{
			return "HTML";
		}

		return $outputType;
	}

	private function getOmitRoot( $omitRoot )
	{
		if( is_null( $omitRoot ) )
		{
			return false;
		}

		return $omitRoot;
	}

	private function getCacheTime( $cacheTime )
	{
		if( is_null( $cacheTime ) )
		{
			return 0;
		}

		return $cacheTime;
	}

	public function importXSL( $data = null, $xslViewFile = null )
	{
		$result = null;

		if( is_null( $xslViewFile ) )
		{
			$this->xslViewFile = Loader::resolve( Loader::viewFolder, $this->xslViewName, Loader::viewExtension );
		}
		else
		{
			$this->xslViewFile = $xslViewFile;
		}

		if( file_exists( $this->xslViewFile ) )
		{
			if( Config::$data[ "enableInlinePHPInViews" ] )
			{
				$result = Loader::parseExternal( $this->xslViewFile, $data );
			}
			else
			{
				$result = Loader::readExternal( $this->xslViewFile );
			}
		}
		else
		{
			if( $this->xslViewName != "" )
			{
				trigger_error( "XSLT view name [" . $this->xslViewName . "] not found", E_USER_ERROR );
			}
			else
			{
				trigger_error( "XSLT view file [" . $this->xslViewFile . "] not found", E_USER_ERROR );
			}
		}

		return $result;
	}

	private function aggregateModelsForView( $data, $omitRoot )
	{
		$xmlHead = self::getXMLHead( $data, $omitRoot );
		$xmlBody = $this->getAggregatedModels();
		$xmlFoot = self::getXMLFoot( $omitRoot );

		return $xmlHead . $xmlBody . $xmlFoot;
	}

	public function getAggregatedModels()
	{
		$stack = "";

		if( is_array( $this->models ) )
		{
			foreach( $this->models as $model )
			{
				if( $model instanceof ModelDriver )
				{
					$stack .= $model->getXMLForAggregation();
				}
				else
				{
					trigger_error( "Invalid model was found in this view's model-stack", E_USER_ERROR );
				}
			}
		}

		return $stack;
	}

	public function processView( $return, $outputType, $cacheTime )
	{
		$result = null;

		if( self::shouldRenderClientSide( $return ) )
		{
			OutputHeaders::XML( $cacheTime );

			echo( $this->getXMLData() );
		}
		else
		{
			$result = $this->transform();

			if( ! $return )
			{
				OutputHeaders::specifically( $outputType, $cacheTime );

				echo( $result );
			}
		}

		return $result;
	}

	private function transform()
	{
		$result = XSLT::transform( $this->getXMLData(), $this->getXSLData(), dirname( $this->xslViewFile ) );

		return $result;
	}

	private function shouldRenderClientSide( $return )
	{
		if( $return === false )
		{
			if( self::isClientSideXSLTSupported() )
			{
				return true;
			}

			if( self::isSourceViewOn() )
			{
				return true;
			}
		}

		return false;
	}

	public function passThru( $data = null, $return = false, $omitRoot = true, $cacheTime = 0 )
	{
		$xmlBody = $this->getAggregatedModels();

		if( $return )
		{
			$xmlResult = $xmlBody;
		}
		else
		{
			$xmlHead = self::getXMLHead( $data, $omitRoot );
			$xmlFoot = self::getXMLFoot( $omitRoot );

			$xmlResult = ( $xmlHead . $xmlBody . $xmlFoot );

			OutputHeaders::XML( $cacheTime );

			echo( $xmlResult );
		}

		return $xmlResult;
	}

	public static function getXMLHead( $data, $omitRoot )
	{
		$encodedData = "";
		$sourceViewAttribute = "";
		$xmlHead = "";

		if( ! is_null( $data ) )
		{
			$encodedData = Normalize::encodeData( $data );
		}

		if( self::isSourceViewOn() )
		{
			$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"" . Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/System::Views::mcc\" ?" . ">\n";

			$sourceViewAttribute = " mcc=\"true\"";
		}
		else
		{
			if( Config::$data[ "enableInlinePHPInViews" ] )
			{
				if( ! is_null( $this->xslViewName ) )
				{
					$fullyQualifiedXsltViewName = str_replace( "\\", "::", Loader::assignDefaultNamespace( $this->xslViewName, null, Loader::viewFolder ) );
					$xmlHead .= "<" . "?xml-stylesheet type=\"text/xsl\" href=\"" . Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ] . "/load/view/" . $fullyQualifiedXsltViewName . $encodedData . "\" ?" . ">\n";
				}
			}
		}

		if( ! $omitRoot )
		{
			$xmlHead .= "<xmvc:root xmlns:xmvc=\"" . self::namespaceXML . "\"" . $sourceViewAttribute . ">\n";
		}

		return $xmlHead;
	}

	public static function getXMLFoot( $omitRoot )
	{
		$xmlFoot = "";

		if( ! $omitRoot )
		{
			$xmlFoot = "</xmvc:root>";
		}

		return $xmlFoot;
	}

	private static function isSourceViewOn()
	{
		return isset( $_GET[ Config::$data[ "sourceViewKey" ] ] ) && Config::$data[ "sourceViewEnabled" ];
	}

	public static function isClientSideXSLTSupported()
	{
		if( Config::$data[ "forceServerSideRendering" ] )
		{
			return false;
		}
		else if( Config::$data[ "forceClientSideRendering" ] )
		{
			return true;
		}
		else
		{
			foreach( Config::$data[ "xsltAgents" ] as $preg )
			{
				if( preg_match( $preg, $_SERVER[ "HTTP_USER_AGENT" ] ) )
				{
					return true;
				}
			}
		}

		return false;
	}
}