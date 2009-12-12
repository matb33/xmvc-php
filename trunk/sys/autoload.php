<?php

function __autoload( $className )
{
	if( ! xMVC\AutoLoad::ModelDriver( $className ) )
	{
		if( ! xMVC\AutoLoad::Library( $className ) )
		{
			trigger_error( "Unable to load a model-driver or library by the name [" . $className . "]", E_USER_ERROR );
		}
	}
}

?>