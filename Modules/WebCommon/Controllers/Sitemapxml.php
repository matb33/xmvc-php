<?php

namespace Modules\WebCommon\Controllers;

class Sitemapxml
{
	public function View( $lang )
	{
		Sitemap::getInstance()->Output( $lang );
	}
}