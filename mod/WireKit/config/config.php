<?php

srand( microtime( true ) * 2785394875 );
define( "NEWLINE", "\r\n" );

$enableInlinePHPInModels = false;
$enableInlinePHPInViews = false;

$isProduction = !preg_match( "/\.local|\.testing\.|\.preview\./", $_SERVER[ "HTTP_HOST" ] );
$isLocal = preg_match( "/\.local/", $_SERVER[ "HTTP_HOST" ] );

if( $isProduction )
{
	error_reporting( 0 );
}
elseif( $isLocal )
{
	error_reporting( E_ALL );
}
else
{
	error_reporting( E_ALL ^ E_NOTICE );
}

$sourceViewEnabled = !$isProduction;