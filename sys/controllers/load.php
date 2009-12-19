<?php

namespace xMVC\Sys;

class Load
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		trigger_error( "Incorrect use of load controller. Specify a type to load, such as 'view'.", E_USER_ERROR );
	}

	public function View()
	{
		$args = func_get_args();

		$data = null;

		if( count( $args ) > 1 )
		{
			$lastArg = $args[ count( $args ) - 1 ];

			if( substr( $lastArg, 0, 5 ) == "_enc_" )
			{
				unset( $args[ count( $args ) - 1 ] );

				$data = substr( $lastArg, 5 );
				$data = str_replace( "_", "=", $data );
				$data = base64_decode( $data );
				$data = unserialize( $data );

				$data[ "encodedData" ] = "/" . $lastArg;
			}
		}

		$xslViewName = str_replace( ( "." . Loader::viewExtension ), "", implode( "/", $args ) );

		// TO-DO: Figure out how this is supposed to map to App, or Mod, or Sys, etc
		$tmpView = new View( Core::namespaceSys . $xslViewName );

		$xmlHead = $tmpView->GetXMLHead( $data, true );
		$xmlFoot = $tmpView->GetXMLFoot( true );

		$xmlString = ( $xmlHead . $tmpView->ImportXSL( $data ) . $xmlFoot );

		OutputHeaders::XML();

		echo( $xmlString );

		unset( $tmpView );
	}
}

?>