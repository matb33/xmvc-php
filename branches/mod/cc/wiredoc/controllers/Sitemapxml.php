<?php

namespace xMVC\Mod\CC;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::Output( $lang );
	}
}

?>