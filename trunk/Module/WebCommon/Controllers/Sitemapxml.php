<?php

namespace Module\WebCommon\Controllers;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::getInstance()->Output( $lang );
	}
}