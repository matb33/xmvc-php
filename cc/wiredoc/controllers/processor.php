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
			$this->RenderPage( $linkData );
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

	private function RenderPage( $linkData )
	{
		$instance = $linkData[ "name" ];
		$component = $linkData[ "component" ];
		$viewName = $linkData[ "view" ];

		if( strlen( trim( $viewName ) ) == 0 )
		{
			$viewName = __NAMESPACE__ . "\\xhtml1-strict";
		}

		$model = new XMLModelDriver( "xMVC\\App\\instances/" . $component . "/" . $instance );
		$view = new View( $viewName );

		$view->PushModel( CC::InjectDependencies( $model ) );
		$view->PushModel( $this->stringData );

		CC::InjectLinkNextToPageName( $view );
		CC::InjectLinkNextToLangSwap( $view );
		CC::InjectLang( $view, $this->lang );

		$view->RenderAsHTML();
	}
}

?>