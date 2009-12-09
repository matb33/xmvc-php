<?php

function __autoload( $className )
{
	$libraryFile = "libraries/" . Normalize::Filename( $className ) . ".php";

	if( ( $classFile = Loader::Prioritize( $libraryFile ) ) !== false )
	{
		require_once( $classFile );
	}
	else
	{
		trigger_error( "Library [" . $className . "] not found", E_USER_ERROR );
	}
}

?>