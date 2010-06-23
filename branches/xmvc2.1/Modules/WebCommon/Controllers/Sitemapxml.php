<?php

namespace Modules\WebCommon\Controllers;

class Sitemapxml
{
	public function view( $lang )
	{
		Sitemap::getInstance()->output( $lang );
	}
}