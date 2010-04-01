<?php

namespace xMVC\Mod\Phpinfo;

use xMVC\Sys\Config;

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