<?php

function __autoload( $className )
{
	$libraryFile = "libraries/" . Normalize::Filename( $className ) . ".php";

	$classFile = Loader::Prioritize( $libraryFile );

	require_once( $classFile );
}

?>