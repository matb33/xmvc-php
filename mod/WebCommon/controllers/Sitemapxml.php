<?php

namespace xMVC\Mod\WebCommon;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::getInstance()->Output( $lang );
	}
}