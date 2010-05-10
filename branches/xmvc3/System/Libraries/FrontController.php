<?php

namespace System\Libraries;

class FrontController extends OverrideableSingleton
{
	protected function __construct()
	{
		parent::__construct();
	}

	public function helloWorld()
	{
		echo( "hello world from the FrontController<br />" );
	}
}