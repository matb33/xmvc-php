<?php

namespace xMVC;

class Load
{
	public static function Index()
	{
		trigger_error( "Incorrect use of load controller. Specify a type to load, such as 'view'.", E_USER_ERROR );
	}

	public static function View()
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

		$xslViewName = str_replace( ( "." . Core::$viewExtension ), "", implode( "/", $args ) );

		$tmpView = new View( $xslViewName );

		$xmlHead = $tmpView->GetXMLHead( $data, true );
		$xmlFoot = $tmpView->GetXMLFoot( true );

		$xmlString = ( $xmlHead . $tmpView->ImportXSL( $data ) . $xmlFoot );

		OutputHeaders::XML();

		echo( $xmlString );

		unset( $tmpView );
	}
}

?>