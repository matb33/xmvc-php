<?php

// re-build example to be a fake "corporate site" (perhaps if designed well, this could be incentive for people to use xMVC as a starter?):
// Home (xhtml-1.0-strict/home)
// About (xhtml-1.0-strict/inside/standard)
// News (xhtml-1.0-strict/inside/articles)
// Careers (xhtml-1.0-strict/inside/articles)
// Contact (xhtml-1.0-strict/inside/contact)
// Customer Area
// Terms of Use (xhtml-1.0-strict/inside/standard)
// Privacy Policy (xhtml-1.0-strict/inside/standard)

namespace Module\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;

use Module\Language\Language;

class Website
{
	protected $lang;
	protected $stringData;

	public function __construct()
	{
		$this->lang = Language::GetLang();

		$this->stringData = new StringsModelDriver();
		$this->stringData->Add( "lang", $this->lang );
	}

	protected function PushDependencies( $page, $model )
	{
		foreach( $model->xPath->query( "//cc:config/cc:dependency" ) as $node )
		{
			$type = $node->getAttribute( "cc:type" );
			$instance = $node->getAttribute( "cc:instance" );

			$page->PushModel( new XMLModelDriver( __NAMESPACE__ . "\\" . $type . "/" . $instance ) );
		}

		return( $page );
	}
}

?>