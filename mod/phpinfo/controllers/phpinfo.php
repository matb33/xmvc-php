<?php

namespace Phpinfo;

use xMVC\Config;

class Phpinfo
{
	public function Index()
	{
		phpinfo();

		echo "<pre>";
		var_dump( Config::$data );
		echo "</pre>";
	}
}