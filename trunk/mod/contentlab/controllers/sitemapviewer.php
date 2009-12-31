<?php

namespace Module\ContentLAB;

use xMVC\Sys\Loader;
use xMVC\Sys\OutputHeaders;

class Sitemapviewer
{
	public function Root( $revision = null )
	{
		if( is_null( $revision ) )
		{
			OutputHeaders::XML();

			readfile( "mod/contentlab/" . Loader::modelFolder . "/sitemap.xml" );
		}
		else
		{
			die( "Fetching sitemap.xml revision " . $revision . " from SVN not yet implemented." );
		}
	}
}

?>