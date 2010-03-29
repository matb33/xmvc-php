<?php

namespace Module\CC;

use xMVC\Sys\Core;
use xMVC\Sys\Routing;
use xMVC\Sys\Loader;
use xMVC\Sys\Config;
use xMVC\Sys\ErrorHandler;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\View;
use xMVC\Sys\Events\DefaultEventDispatcher;

/*

NOTES:
1) Notice that Processor is hard-coded to extend \xMVC\App\Website.  For the time being, this is a requirement of the CC module. You will need to create this Website class in your app/controllers.
2) Notice the protected function Call.  This is to be optionally called from your custom controller (called by writing a higher precedence route) to continue regular Processor operation.

*/

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
		$viewName = $this->FallbackViewNameIfNecessary( $linkData[ "view" ] );

		$view = new View( $viewName );

		$this->PushInstance( $view, $component, $instance );
		$this->PushXLIFF( $view, $component, $instance );
		$this->PushStringData( $view, $component, $instance, $viewName );
		$this->PushAdditionalModels( $view );

		CC::InjectLinkNextToPageName( $view );
		CC::InjectLinkNextToLangSwap( $view );
		CC::InjectLang( $view, $this->lang );

		$view->RenderAsHTML();
	}

	private function FallbackViewNameIfNecessary( $viewName )
	{
		if( strlen( trim( $viewName ) ) == 0 )
		{
			$viewName = __NAMESPACE__ . "\\xhtml1-strict";
		}

		return( $viewName );
	}

	private function PushInstance( &$view, $component, $instance )
	{
		$model = new XMLModelDriver( Core::namespaceApp . "instances/" . $component . "/" . $instance );
		CC::InjectReferences( $model );
		$view->PushModel( $model );
	}

	private function PushXLIFF( &$view, $component, $instance )
	{
		if( XMLModelDriver::Exists( Core::namespaceApp . "instances/" . $component . "/xliff/" . $instance . "." . $this->lang, "xliff" ) )
		{
			$xliffModel = new XMLModelDriver( Core::namespaceApp . "instances/" . $component . "/xliff/" . $instance . "." . $this->lang . ".xliff" );
			$view->PushModel( $xliffModel );
		}
	}

	private function PushStringData( &$view, $component, $instance, $viewName )
	{
		$basePath = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ];
		$link = $basePath . Routing::URI();

		$stringData = new StringsModelDriver();
		$stringData->Add( "component", $component );
		$stringData->Add( "instance", $instance );
		$stringData->Add( "instance-file", $instance . "." . Loader::modelExtension );
		$stringData->Add( "view-name", $viewName );
		$stringData->Add( "base-path", $basePath );
		$stringData->Add( "http-host", $_SERVER[ "HTTP_HOST" ] );
		$stringData->Add( "uri", Routing::URI() );
		$stringData->Add( "link", $link );
		$stringData->Add( "link-urlencoded", urlencode( $link ) );

		if( isset( Config::$data[ "isProduction" ] ) && Config::$data[ "isProduction" ] )
		{
			$stringData->Add( "cache-buster", md5( $_SERVER[ "HTTP_HOST" ] ) );
		}
		else
		{
			$stringData->Add( "cache-buster", md5( date( "Y-m-d H:i:s" ) . rand( 0, 9999 ) ) );
		}

		$view->PushModel( $stringData );
	}

	private function PushAdditionalModels( &$view )
	{
		foreach( $this->additionalModels as $additionalModel )
		{
			$view->PushModel( $additionalModel );
		}
	}
}

?>