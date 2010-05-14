<?php

spl_autoload_extensions( ".php" );
spl_autoload_register();

if( isset( $rootNamespaceFolders ) && is_array( $rootNamespaceFolders ) )
{
	$path = get_include_path();

	foreach( $rootNamespaceFolders as $folder )
	{
		$path .= ( PATH_SEPARATOR . $folder );
	}

	set_include_path( $path );
}