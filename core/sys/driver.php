<?php

class ModelDriver extends DOMDocument
{
	public $xPath;
	public $autoXPath;
	public $autoRegisterNamespaces;

	function ModelDriver()
	{
		parent::__construct();

		$this->preserveWhiteSpace		= true;
		$this->formatOutput				= true;
		$this->autoXPath				= true;
		$this->autoRegisterNamespaces	= true;
	}

	// DOMDocument method overrides

	public function loadXML( $source, $options = 0 )
	{
		parent::loadXML( $source, $options );

		$this->RefreshXPath( $source );
	}

	// Model driver methods

	public function RefreshXPath( $source = null )
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
			$this->xPath->registerNamespace( "xmvc", "http://www.xmvc.org/ns/xmvc/1.0" );
		}

		$this->xPath->registerNamespace( "xhtml", "http://www.w3.org/1999/xhtml" );
	}

	public function LoadModelXML( $xmlModelFile, $data = null, $preservePHP = false )
	{
		$xml = null;

		if( file_exists( $xmlModelFile ) )
		{
			if( $preservePHP )
			{
				$xml = file_get_contents( $xmlModelFile );
			}
			else
			{
				if( is_array( $data ) )
				{
					// bring variables from data array into local scope

					foreach( $data as $key => $value )
					{
						eval( "\$$key = \$value;" );
					}
				}

				ob_start();

				include( $xmlModelFile );

				$xml = ob_get_contents();

				ob_end_clean();
			}
		}
		else
		{
			trigger_error( "XML model file '" . $xmlModelFile . "' not found", E_USER_ERROR );
		}

		return( $xml );
	}

	public function SetXML( $xml )
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

		$xmlString = xMVC::StripRootTags( $xmlString );

		return( $xmlString );
	}
}

?>