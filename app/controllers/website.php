<?php

namespace xMVC\App;

use xMVC\Sys\Loader;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;

use Module\Language\Language;

class Website
{
	protected $commonContent;
	protected $lang;
	protected $stringData;

	public function __construct()
	{
		$this->lang = Language::GetLang();

		$this->commonContent = new XMLModelDriver( "content/" . $this->lang . "/common" );

		$this->stringData = new StringsModelDriver();
		$this->stringData->Add( "lang", $this->lang );
	}
}

?>