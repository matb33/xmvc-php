<?php

spl_autoload_extensions( ".php" );
spl_autoload_register();

if( isset( $moduleRegistry ) && is_array( $moduleRegistry ) )
{
	$path = get_include_path();

	foreach( $moduleRegistry as $folder )
	{
		$path .= ( PATH_SEPARATOR . $folder );
	}

	set_include_path( $path );
}