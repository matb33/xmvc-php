<?php

function __autoload( $className )
{
	$libraryFile = "libraries/" . xMVC::NormalizeName( $className ) . ".php";

	$applicationLibraryFile	= APP_PATH . $libraryFile;
	$systemLibraryFile		= SYS_PATH . $libraryFile;

	$classFile = null;

	if( file_exists( $applicationLibraryFile ) )
	{
		$classFile = $applicationLibraryFile;
	}
	else if( file_exists( $systemLibraryFile ) )
	{
		$classFile = $systemLibraryFile;
	}

	require_once( $classFile );
}

?>