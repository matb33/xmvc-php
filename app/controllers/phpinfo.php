<?php

class Phpinfo
{
	public function Index()
	{
		var_dump( Config::Load( SYS_PATH ) );

		echo "Client-side XSLT supported? [" . (int)xMVC::IsClientSideXSLTSupported() . "]";

		phpinfo();
	}
}