<?php

namespace Libraries;

//use System\Libraries\Routing;

class MyRouting
{
	protected function __construct()
	{
		parent::__construct();
	}

	public function helloWorld()
	{
		echo( "hello world from MyRouting<br />" );
	}
}