<?php

namespace xMVC;

use Language\Language;

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