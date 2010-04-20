<?php

namespace xMVC\Mod\WireKit;

class Sitemapxml
{
	public function View( $lang )
	{
		$lookup = new ComponentLookup();
		$sitemap = new Sitemap( $lookup->Get() );
		$sitemap->Output( $lang );
	}
}

?>