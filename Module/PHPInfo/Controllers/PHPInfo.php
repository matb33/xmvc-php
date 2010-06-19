<?php

namespace Module\PHPInfo\Controllers;

use System\Libraries\Config;

class PHPInfo
{
	public function Index()
	{
		phpinfo();

		echo "<pre>";
		var_dump( Config::$data );
		echo "</pre>";
	}
}