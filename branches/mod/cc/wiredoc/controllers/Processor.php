<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\Core;
use xMVC\Sys\Routing;
use xMVC\Sys\Loader;
use xMVC\Sys\Config;
use xMVC\Sys\ErrorHandler;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\View;
use xMVC\Sys\Normalize;
use xMVC\Sys\Events\Event;
use xMVC\Sys\Events\DefaultEventDispatcher;

use xMVC\Mod\Language\Language;
use xMVC\Mod\Utils\StringUtils;

class Processor
{
	private $application;
	private $view;
	private $modelStack;

	public function __construct()
	{
		$this->modelStack = array();
		$this->application = new Config::$data[ "applicationClass" ]( $this->modelStack );
	}

	protected function Call()
	{
		$pathParts = $this->GetPathParts();

		call_user_func_array( "self::Page", $pathParts );
	}

	protected function GetPathParts()
	{
		$pathParts = Routing::GetPathParts();
		$pathParts[ 0 ] = Loader::StripNamespace( $pathParts[ 0 ] );

		return( $pathParts );
	}

	public function Page()
	{
		$currentPath = "/" . ( func_num_args() ? implode( "/", func_get_args() ) . "/" : "" );

		if( ( $linkData = Sitemap::GetLinkDataFromSitemapByPath( $currentPath ) ) !== false )
		{
			$this->RenderPageWithLinkData( $linkData );
		}
		else
		{
			$this->Invoke404( $currentPath );
		}
	}

	private function Invoke404( $path )
	{
		ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => __CLASS__, "method" => $path ) );
	}

	public function RenderPageWithModel( $model, $component, $instanceName )
	{
		$viewName = $model->xPath->query( "//component:definition/@view" )->item( 0 )->nodeValue;
		$viewName = $this->FallbackViewNameIfNecessary( $viewName );

		$this->view = new View( $viewName );
		CC::InjectReferences( $model );
		$this->view->PushModel( $model );

		$this->RenderPage( $component, $instanceName, $viewName );
	}

	public function RenderPageWithLinkData( $linkData )
	{
		$instanceName = $linkData[ "name" ];
		$component = $linkData[ "component" ];
		$viewName = $this->FallbackViewNameIfNecessary( $linkData[ "view" ] );

		$this->view = new View( $viewName );
		$this->PushInstance( $component, $instanceName );

		$this->RenderPage( $component, $instanceName, $viewName );
	}

	public function OnComponentInstanceGenerated( Event $event )
	{
		$model = $event->arguments[ "model" ];
		$component = $event->arguments[ "component" ];
		$instanceName = $event->arguments[ "instanceName" ];

		$this->RenderPageWithModel( $model, $component, $instanceName );
	}

	private function RenderPage( $component, $instanceName, $viewName )
	{
		$this->PushXLIFF( $component, $instanceName );
		$this->PushStringData( $component, $instanceName, $viewName );
		$this->PushModelStack();

		CC::InjectHref( $this->view );
		CC::InjectLang( $this->view, Language::GetLang() );

		$this->view->RenderAsHTML();
	}

	private function FallbackViewNameIfNecessary( $viewName )
	{
		if( strlen( trim( $viewName ) ) == 0 )
		{
			$viewName = __NAMESPACE__ . "\\xhtml1-strict";
		}

		return( $viewName );
	}

	private function PushInstance( $component, $instanceName )
	{
		$modelName = StringUtils::ReplaceTokensInPattern( Config::$data[ "componentInstanceFilePattern" ], array( "component" => $component, "instance" => $instanceName ) );
		$model = new XMLModelDriver( $modelName );
		CC::InjectReferences( $model );
		$this->view->PushModel( $model );
	}

	private function PushXLIFF( $component, $instanceName )
	{
		$filename = StringUtils::ReplaceTokensInPattern( Config::$data[ "xliffFilePattern" ], array( "component" => $component, "instance" => $instanceName ) );
		$pathInfo = pathinfo( $filename );

		if( XMLModelDriver::Exists( $pathInfo[ "dirname" ] . "/" . $pathInfo[ "filename" ], $pathInfo[ "extension" ] ) )
		{
			$xliffModel = new XMLModelDriver( $filename );
			$this->view->PushModel( $xliffModel );
		}
	}

	private function PushStringData( $component, $instanceName, $viewName )
	{
		$basePath = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ];
		$uri = Normalize::StripQueryInURI( Routing::URI() );
		$link = $basePath . $uri;

		$stringData = new StringsModelDriver();
		$stringData->Add( "lang", Language::GetLang() );
		$stringData->Add( "component", $component );
		$stringData->Add( "instance", $instanceName );
		$stringData->Add( "instance-file", $instanceName . "." . Loader::modelExtension );
		$stringData->Add( "view-name", $viewName );
		$stringData->Add( "base-path", $basePath );
		$stringData->Add( "http-host", $_SERVER[ "HTTP_HOST" ] );
		$stringData->Add( "uri", $uri );
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

		$this->view->PushModel( $stringData );
		$this->view->PushModel( new HierarchyModelDriver( $component, $instanceName ) );
	}

	private function PushModelStack()
	{
		foreach( $this->modelStack as $model )
		{
			$this->view->PushModel( $model );
		}
	}
}

?>