<?php

use xMVC\Sys\AutoLoad;

function __autoload( $className )
{
	if( AutoLoad::Controller( $className ) === false )
	{
		if( AutoLoad::ModelDriver( $className ) === false )
		{
			if( AutoLoad::Library( $className ) === false )
			{
				trigger_error( "Unable to load a controller, model-driver nor a library by the name [" . $className . "]", E_USER_ERROR );
			}
		}
	}
}

?>