<?php

namespace Module\CC;

use xMVC\Sys\Routing;
use xMVC\Sys\Loader;
use xMVC\Sys\ErrorHandler;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\View;

class Processor extends \xMVC\App\Website
{
	public function __construct()
	{
		parent::__construct();
	}

	protected function Call()
	{
		$pathParts = Routing::GetPathParts();
		$pathParts[ 0 ] = Loader::StripNamespace( $pathParts[ 0 ] );

		call_user_func_array( "self::Page", $pathParts );
	}

	public function Page()
	{
		$currentPath = "/" . ( func_num_args() ? implode( "/", func_get_args() ) . "/" : "" );

		if( ( $linkData = Sitemap::GetLinkDataFromSitemapByPath( $currentPath ) ) !== false )
		{
			$this->RenderPageAsHTML( $linkData );
		}
		else
		{
			$this->Invoke404();
		}
	}

	private function Invoke404()
	{
		ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => __CLASS__, "method" => $currentPath ) );
	}

	private function RenderPageAsHTML( $linkData )
	{
		$instance = $linkData[ "name" ];
		$component = $linkData[ "component" ];

		$model = new XMLModelDriver( "xMVC\\App\\instances/" . $component . "/" . $instance );
		$view = new View( "xMVC\\App\\" . $component );

		$view->PushModel( CC::InjectDependencies( $model ) );
		$view->PushModel( $this->stringData );

		CC::InjectLinkNextToPageName( $view );
		CC::InjectLinkNextToLangSwap( $view );
		CC::InjectLang( $view, $this->lang );

		$view->RenderAsHTML();
	}
}

?>