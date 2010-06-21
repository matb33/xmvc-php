<?php

namespace System\Libraries;

// TO-DO: Make this an abstract class where TransformForeignToXML is abstract

abstract class ModelDriver extends \DOMDocument
{
	public $xPath;
	protected $rootElement;
	private $modelDebugInformation = array();

	public function __construct()
	{
		parent::__construct( "1.0", "UTF-8" );

		$this->preserveWhiteSpace = Config::$data[ "modelDriverPreserveWhiteSpace" ];
		$this->formatOutput = Config::$data[ "modelDriverFormatOutput" ];
	}

	public function loadXML( $source, $options = 0 )
	{
		// DOMDocument method override
		if( parent::loadXML( $source, $options ) === false )
		{
			$this->dumpDebugInformation();
		}

		$this->refreshXPath( $source );
	}

	protected function transformForeignToXML()
	{
		$this->refreshXPath();
	}

	private function refreshXPath( $source = null )
	{
		$this->xPath = new \DOMXpath( $this );

		if( isset( Config::$data[ "enableXSLTPHPFunctions" ] ) )
		{
			if( Config::$data[ "enableXSLTPHPFunctions" ] )
			{
				if( isset( Config::$data[ "restrictXSLTPHPFunctions" ] ) && is_array( Config::$data[ "restrictXSLTPHPFunctions" ] ) && count( Config::$data[ "restrictXSLTPHPFunctions" ] ) )
				{
					XSLT::XSLTPHPFunctionInvocationHack();
					$this->xPath->registerPHPFunctions( Config::$data[ "restrictXSLTPHPFunctions" ] );
				}
				else
				{
					$this->xPath->registerPHPFunctions();
				}
			}
		}

		$this->registerNamespaces( $source );
	}

	private function registerNamespaces( $source )
	{
		if( is_null( $source ) )
		{
			$source = $this->getXML();
		}

		preg_match_all( "/xmlns:(.+?)=\"(.+?)\"/", $source, $matches, PREG_SET_ORDER );

		$namespaces = array();

		foreach( $matches as $declaration )
		{
			$namespaces[ $declaration[ 1 ] ] = $declaration[ 2 ];
		}

		foreach( $namespaces as $name => $url )
		{
			$this->xPath->registerNamespace( $name, $url );
		}

		$this->xPath->registerNamespace( "xhtml", "http://www.w3.org/1999/xhtml" );
		$this->xPath->registerNamespace( "php", "http://php.net/xpath" );
	}

	protected function loadModelXML( $xmlModelFile, $data = null )
	{
		if( Config::$data[ "enableInlinePHPInModels" ] )
		{
			$xml = Loader::parseExternal( $xmlModelFile, $data );
		}
		else
		{
			$xml = Loader::readExternal( $xmlModelFile );
		}

		return Normalize::stripXMLRootTags( $xml );
	}

	public function setXML( $xml )
	{
		$completeXML  = View::getXMLHead( null, false );
		$completeXML .= Normalize::stripXMLRootTags( $xml );
		$completeXML .= View::getXMLFoot( false );

		$this->loadXML( $completeXML );

		return $completeXML;
	}

	public function getXMLForAggregation()
	{
		return $this->getXML( true );
	}

	public function getCompleteXML()
	{
		return $this->getXML( false );
	}

	protected function getXML( $stripRootTags = false )
	{
		$completeXML = $this->saveXML( $this->documentElement );

		if( $stripRootTags )
		{
			return Normalize::stripXMLRootTags( $completeXML );
		}
		else
		{
			return $completeXML;
		}
	}

	public function dump( $exit = false, $caption = "MODEL XML DUMP", $rootNode = null, $height = "300" )
	{
		if( is_null( $rootNode ) )
		{
			$XML = $this->saveXML();
		}
		else
		{
			$XML = $this->saveXML( $rootNode );
		}

		echo( "<fieldset style=\"padding:5px 10px 10px 10px;width:960px;background-color:#666;\"><legend style=\"font-weight:bold;background-color:#666;color:#fff;padding:5px 10px 0px 10px;\">" . $caption . "</legend>" );
		echo( "<textarea wrap=\"off\" style=\"font:12px Courier New;width:960px;height:" . $height . "px;background-color:#fff;\">" . htmlentities( $XML ) . "</textarea>" );
		echo( "</fieldset>" );

		if( $exit )
		{
			exit();
		}
	}

	public function dumpDebugInformation( $exit = false, $height = "300" )
	{
		echo( "<fieldset style=\"padding:5px 10px 10px 10px;width:960px;background-color:#666;\"><legend style=\"font-weight:bold;background-color:#666;color:#fff;padding:5px 10px 0px 10px;\">Model debug information:</legend>" );
		echo( "<textarea wrap=\"off\" style=\"font:12px Courier New;width:960px;height:" . $height . "px;background-color:#fff;\">" . print_r( $this->modelDebugInformation, true ) . "</textarea>" );
		echo( "</fieldset>" );
	}

	public function pushDebugInformation( $key, $value )
	{
		$this->modelDebugInformation[ $key ] = print_r( $value, true );
	}
}