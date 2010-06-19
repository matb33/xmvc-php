<?php

namespace Module\WebCommon;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::getInstance()->Output( $lang );
	}
}