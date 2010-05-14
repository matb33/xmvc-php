<?php

namespace Libraries;

class Routing extends OverrideableSingleton
{
	protected function __construct()
	{
		parent::__construct();
	}

	public function helloWorld()
	{
		echo( "hello world from the Routing<br />" );
	}
}