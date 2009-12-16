<?php

function __autoload( $className )
{
	if( xMVC\AutoLoad::ModelDriver( $className ) === false )
	{
		if( xMVC\AutoLoad::Library( $className ) === false )
		{
			trigger_error( "Unable to load a model-driver or library by the name [" . $className . "]", E_USER_ERROR );
		}
	}
}

?>