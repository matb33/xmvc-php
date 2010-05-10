<?php

spl_autoload_extensions( ".php" );
spl_autoload_register();

if( isset( $projectRegistry ) && is_array( $projectRegistry ) )
{
	foreach( $projectRegistry as $folder )
	{
		set_include_path( get_include_path() . PATH_SEPARATOR . $folder );
	}
}