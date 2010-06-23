<?php

namespace Modules\PHPInfo\Controllers;

use System\Libraries\Config;

class PHPInfo
{
	public function index()
	{
		phpinfo();

		echo "<pre>";
		var_dump( Config::$data );
		echo "</pre>";
	}
}