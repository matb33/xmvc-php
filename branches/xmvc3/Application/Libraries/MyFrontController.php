<?php

namespace Application\Libraries;

use System\Libraries\FrontController;

class MyFrontController extends FrontController
{
	protected function __construct()
	{
		parent::__construct();
	}

	public function helloWorld()
	{
		echo "hello world from MyFrontController<br />";
	}
}