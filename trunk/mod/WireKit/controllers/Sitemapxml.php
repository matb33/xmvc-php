<?php

namespace xMVC\Mod\WireKit;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::Output( $lang );
	}
}

?>