<?php

namespace Application\Libraries;

use System\Libraries\Routing;

class MyRouting extends Routing
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