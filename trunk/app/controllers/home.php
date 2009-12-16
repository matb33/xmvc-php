<?php

namespace xMVC;

use Language\Language;

class Home
{
	public static function Index()
	{
		$lang = Language::GetLang();

		$commonContent = new XMLModelDriver( "content/" . $lang . "/common" );
		$pageContent = new XMLModelDriver( "content/" . $lang . "/home" );

		$data = new StringsModelDriver();
		$data->Add( "lang", $lang );

		$controllers = new FilesystemModelDriver();
		$controllers->GetFileList( APP_PATH . "controllers", "/\.php/" );

		$page = new View( "home" );
		$page->PushModel( $commonContent );
		$page->PushModel( $pageContent );
		$page->PushModel( $data );
		$page->PushModel( $controllers );
		$page->RenderAsHTML();
	}
}

?>