<?php

namespace xMVC;

class Phpinfo
{
	public static function Index()
	{
		var_dump( Config::Load( SYS_PATH ) );

		echo "Client-side XSLT supported? [" . (int)Core::IsClientSideXSLTSupported() . "]";

		phpinfo();
	}
}