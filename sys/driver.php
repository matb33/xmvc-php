<?php

namespace xMVC;

class ModelDriver extends \DOMDocument
{
	public $xPath;
	protected $rootElement;

	public function __construct()
	{
		parent::__construct( "1.0", "UTF-8" );

		$this->preserveWhiteSpace		= true;
		$this->formatOutput				= true;
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
			$xml = Loader::ReadExternal( $xmlModelFile, $data );
		}

		return( $this->StripRootTags( $xml ) );
	}

	protected function SetXML( $xml )
	{
		$completeXML  = View::GetXMLHead( null, false );
		$completeXML .= $xml;
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
			return( $this->StripRootTags( $completeXML ) );
		}
		else
		{
			return( $completeXML );
		}
	}

	protected static function StripRootTags( $xml )
	{
		// Strip xml declaration
		$xml = preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xml );

		// Strip xmvc:root
		$xml = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xml );
		$xml = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xml );

		return( $xml );
	}
}

interface ModelDriverInterface
{
	public function TransformForeignToXML();
}

?>