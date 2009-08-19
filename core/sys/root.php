<?php

//==============================================================================
// Define Root class, which every class eventually extends
//==============================================================================

class Root
{
	var $version = null;

	function Root()
	{
		$this->version = "0.0.1";	// Using version only to demonstrate the type of variable that Root could hold
	}
}

?>