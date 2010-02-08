<?php

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\OutputHeaders;
use xMVC\Sys\Config;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::Output( $lang );
	}
}

?>