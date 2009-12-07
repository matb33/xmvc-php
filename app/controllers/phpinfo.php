<?php

class Phpinfo extends Controller
{
	public function __construct()
	{
		parent::__construct();
	}

	public function Index()
	{
		var_dump( Config::Load( SYS_PATH ) );

		echo "Client-side XSLT supported? [" . (int)xMVC::IsClientSideXSLTSupported() . "]";

		phpinfo();
	}
}