<?php

namespace xMVC;

class Phpinfo
{
	public static function Index()
	{
		echo "<pre>";
		var_dump( Config::$data );

		phpinfo();
	}
}