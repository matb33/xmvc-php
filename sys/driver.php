<?php

class ModelDriver extends DOMDocument
{
	public $xPath;

	private $autoXPath;
	private $autoRegisterNamespaces;

	public function __construct()
	{
		parent::__construct();

		$this->preserveWhiteSpace		= true;
		$this->formatOutput				= true;
		$this->autoXPath				= true;
		$this->autoRegisterNamespaces	= true;
	}

	public function loadXML( $source, $options = 0 )
	{
		// DOMDocument method override

		parent::loadXML( $source, $options );

		$this->RefreshXPath( $source );
	}

	private function RefreshXPath( $source = null )
	{
		$this->xPath = new DOMXpath( $this );

		if( $this->autoRegisterNamespaces && !is_null( $source ) )
		{
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
		}
		else
		{
			$this->xPath->registerNamespace( "xmvc", xMVC::$namespace );
		}

		$this->xPath->registerNamespace( "xhtml", "http://www.w3.org/1999/xhtml" );
	}

	protected function LoadModelXML( $xmlModelFile, $data = null, $preservePHP = false )
	{
		$xml = null;

		if( file_exists( $xmlModelFile ) )
		{
			if( $preservePHP )
			{
				$xml = Loader::ReadExternal( $xmlModelFile, $data );
			}
			else
			{
				$xml = Loader::ParseExternal( $xmlModelFile, $data );
			}
		}
		else
		{
			trigger_error( "XML model file '" . $xmlModelFile . "' not found", E_USER_ERROR );
		}

		return( $xml );
	}

	protected function SetXML( $xml )
	{
		$view = new View();

		$xmlString = "";
		$xmlString .= $view->GetXMLHead( "", null, false );
		$xmlString .= $xml;
		$xmlString .= $view->GetXMLFoot( false );

		$this->loadXML( $xmlString );

		return( $xmlString );
	}

	public function GetXML( $stripRootTags = false )
	{
		$xmlString = $this->saveXML( $this->documentElement );

		if( $stripRootTags )
		{
			$xmlString = $this->StripRootTags( $xmlString );
		}

		return( $xmlString );
	}

	protected static function StripRootTags( $xmlData )
	{
		// Strip XML declaration
		$xmlData = preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xmlData );

		// Strip xmvc:root
		$xmlData = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xmlData );
		$xmlData = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xmlData );

		return( $xmlData );
	}
}

?>