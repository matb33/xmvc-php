<?php

namespace xMVC\Mod\Phpinfo;

use System\Libraries\Config;

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