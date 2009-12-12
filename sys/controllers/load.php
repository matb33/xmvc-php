<?php

namespace xMVC;

class Load
{
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

		$tmpView = new View( $xslViewName );

		$xmlHead = $tmpView->GetXMLHead( $data, true );
		$xmlFoot = $tmpView->GetXMLFoot( true );

		$xslViewName = str_replace( ".xsl", "", implode( "/", $args ) );

		$xmlString = ( $xmlHead . $tmpView->ImportXSL( $data ) . $xmlFoot );

		OutputHeaders::XML();

		echo( $xmlString );

		unset( $tmpView );
	}
}

?>