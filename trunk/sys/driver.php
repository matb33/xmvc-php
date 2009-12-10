<?php

class ModelDriver extends DOMDocument
{
	public $xPath;

	private $autoXPath;
	private $autoRegisterNamespaces;

	protected $rootElement;

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

	protected function TransformForeignToXML()
	{
		$this->RefreshXPath();
	}

	private function RefreshXPath( $source = null )
	{
		$this->xPath = new DOMXpath( $this );

		if( is_null( $source ) )
		{
			$source = $this->saveXML();
		}

		if( $this->autoRegisterNamespaces )
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

	protected function LoadModelXML( $xmlModelFile, $data = null )
	{
		if( Config::$data[ "enableInlinePHPInModels" ] )
		{
			$xml = $this->StripRootTags( Loader::ParseExternal( $xmlModelFile, $data ) );
		}
		else
		{
			$xml = $this->StripRootTags( Loader::ReadExternal( $xmlModelFile, $data ) );
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
		$xmlString = $this->saveXML( $this->documentElement );

		if( $stripRootTags )
		{
			$xmlString = $this->StripRootTags( $xmlString );
		}

		return( $xmlString );
	}

	protected static function StripRootTags( $xmlData )
	{
		// Strip xml declaration
		$xmlData = preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xmlData );

		// Strip xmvc:root
		$xmlData = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xmlData );
		$xmlData = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xmlData );

		return( $xmlData );
	}
}

interface ModelDriverInterface
{
	public function TransformForeignToXML();
}

?>