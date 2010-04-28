<?php

namespace xMVC\Mod\WireKit;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::getInstance()->Output( $lang );
	}
}

?>