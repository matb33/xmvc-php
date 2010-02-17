<?php

namespace Module\CC;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::Output( $lang );
	}
}

?>