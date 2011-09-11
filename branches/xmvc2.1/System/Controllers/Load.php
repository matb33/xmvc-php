<?php

namespace System\Controllers;

use System\Libraries\Loader;
use System\Libraries\View;
use System\Libraries\OutputHeaders;

class Load
{
	public function index()
	{
		trigger_error( "Incorrect use of load controller. Specify a type to load, such as 'view'.", E_USER_ERROR );
	}

	public function view()
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
		$xslViewName = str_replace( "::", "\\", $xslViewName );

		$tmpView = new View( $xslViewName );

		$xmlHead = $tmpView->getXMLHead( $data, true );
		$xmlFoot = $tmpView->getXMLFoot( true );

		$xmlString = ( $xmlHead . $tmpView->importXSL( $data ) . $xmlFoot );

		OutputHeaders::XML( 3600 );

		echo( $xmlString );

		unset( $tmpView );
	}
}