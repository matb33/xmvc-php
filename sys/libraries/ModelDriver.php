<?php

namespace xMVC\Sys;

// TO-DO: Make this an abstract class where TransformForeignToXML is abstract

class ModelDriver extends \DOMDocument
{
	public $xPath;
	protected $rootElement;

	public function __construct()
	{
		parent::__construct( "1.0", "UTF-8" );

		$this->preserveWhiteSpace = true;
		$this->formatOutput = true;
	}

	public function loadXML( $source, $options = 0 )
	{
		// DOMDocument method override
		parent::loadXML( $source, $options );

		$this->RefreshXPath( $source );
	}

	protected function TransformForeignToXML()
	{
		$this->RefreshXPath();
	}

	private function RefreshXPath( $source = null )
	{
		$this->xPath = new \DOMXpath( $this );
		$this->RegisterNamespaces( $source );
	}

	private function RegisterNamespaces( $source )
	{
		if( is_null( $source ) )
		{
			$source = $this->GetXML();
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
	}

	protected function LoadModelXML( $xmlModelFile, $data = null )
	{
		if( Config::$data[ "enableInlinePHPInModels" ] )
		{
			$xml = Loader::ParseExternal( $xmlModelFile, $data );
		}
		else
		{
			$xml = Loader::ReadExternal( $xmlModelFile );
		}

		return( Normalize::StripXMLRootTags( $xml ) );
	}

	public function SetXML( $xml )
	{
		$completeXML  = View::GetXMLHead( null, false );
		$completeXML .= Normalize::StripXMLRootTags( $xml );
		$completeXML .= View::GetXMLFoot( false );

		$this->loadXML( $completeXML );

		return( $completeXML );
	}

	public function GetXMLForStacking()
	{
		return( $this->GetXML( true ) );
	}

	public function GetCompleteXML()
	{
		return( $this->GetXML( false ) );
	}

	protected function GetXML( $stripRootTags = false )
	{
		$completeXML = $this->saveXML( $this->documentElement );

		if( $stripRootTags )
		{
			return( Normalize::StripXMLRootTags( $completeXML ) );
		}
		else
		{
			return( $completeXML );
		}
	}
}

interface ModelDriverInterface
{
	public function TransformForeignToXML();
}

?>