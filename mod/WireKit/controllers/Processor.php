<?php

namespace xMVC\Mod\WireKit;

use xMVC\Sys\Core;
use xMVC\Sys\Routing;
use xMVC\Sys\Loader;
use xMVC\Sys\Config;
use xMVC\Sys\ErrorHandler;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\View;
use xMVC\Sys\Normalize;
use xMVC\Sys\Delegate;
use xMVC\Sys\Events\Event;
use xMVC\Sys\Events\DefaultEventDispatcher;

use xMVC\Mod\Language\Language;
use xMVC\Mod\Utils\StringUtils;
use xMVC\Mod\WireKit\Components\ComponentLookup;
use xMVC\Mod\WireKit\Components\ComponentFactory;
use xMVC\Mod\WireKit\Components\ComponentUtils;

class Processor
{
	private $application;
	private $view;
	private $modelStack;

	public function __construct()
	{
		$this->modelStack = array();
		$this->application = new Config::$data[ "applicationClass" ]( $this->modelStack );

		if( Config::$data[ "isLocal" ] || ComponentLookup::getInstance()->HostsDontMatch() )
		{
			ComponentLookup::getInstance()->Generate();
		}
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

		if( ( $componentData = ComponentLookup::getInstance()->GetComponentDataByPath( $currentPath ) ) !== false )
		{
			$this->RenderPageUsingComponentData( $componentData );
		}
		else
		{
			$this->Invoke404( $currentPath );
		}
	}

	public function RenderComponent( $fullyQualifiedName, $eventName = null, $parameters = array(), $delegate = null, $cacheMinutes = 0 )
	{
		if( ( $componentData = ComponentLookup::getInstance()->GetComponentDataByFullyQualifiedName( $fullyQualifiedName ) ) !== false )
		{
			$componentData[ "eventName" ] = $eventName;
			$componentData[ "parameters" ] = $parameters;
			$componentData[ "cacheMinutes" ] = $cacheMinutes;

			$this->RenderPageUsingComponentData( $componentData, $delegate );
		}
		else
		{
			$this->Invoke404( $fullyQualifiedName );
		}
	}

	public function RenderPageUsingComponentData( $componentData, $delegate = null )
	{
		$component = $componentData[ "component" ];
		$instanceName = isset( $componentData[ "instanceName" ] ) ? $componentData[ "instanceName" ] : null;
		$eventName = isset( $componentData[ "eventName" ] ) ? $componentData[ "eventName" ] : null;
		$parameters = isset( $componentData[ "parameters" ] ) ? $componentData[ "parameters" ] : array();
		$cacheMinutes = isset( $componentData[ "cacheMinutes" ] ) ? $componentData[ "cacheMinutes" ] : 0;
		$matchingLang = isset( $componentData[ "matchingLang" ] ) && strlen( $componentData[ "matchingLang" ] ) > 0 ? $componentData[ "matchingLang" ] : null;

		if( ! is_null( $matchingLang ) )
		{
			Language::SetLang( $matchingLang );
		}

		if( is_null( $delegate ) )
		{
			$delegate = new Delegate( "OnComponentReadyForRender", $this );
		}

		$factory = new ComponentFactory();
		$factory->addEventListener( "onreadyforrender.components", $delegate );
		$factory->GetComponent( $component, $instanceName, $eventName, $parameters, $cacheMinutes );
	}

	public function OnComponentReadyForRender( Event $event )
	{
		$model = $event->arguments[ "model" ];
		$component = $event->arguments[ "component" ];
		$instanceName = $event->arguments[ "instanceName" ];

		$this->RenderPageWithModel( $model, $component, $instanceName );
	}

	private function Invoke404( $path )
	{
		ErrorHandler::InvokeHTTPError( array( "errorCode" => "404", "controllerFile" => __CLASS__, "method" => $path ) );
	}

	public function RenderPageWithModel( $model, $component, $instanceName )
	{
		$viewNameNodeList = $model->xPath->query( "//meta:view | //wd:meta[ @wd:name='view' ]" );
		$viewName = $viewNameNodeList->length > 0 ? $viewNameNodeList->item( 0 )->nodeValue : "";
		$viewName = ComponentUtils::FallbackViewNameIfNecessary( $viewName );

		$this->view = new View( $viewName );
		$this->view->PushModel( $model );

		list( $hrefContextComponent, $hrefContextInstanceName ) = ComponentUtils::GetHrefContextComponentAndInstanceName( $model );
		$this->PushHierarchy( $hrefContextComponent, $hrefContextInstanceName );
		$this->RenderPage( $component, $instanceName, $viewName );
	}

	private function RenderPage( $component, $instanceName, $viewName )
	{
		$this->PushXLIFF( $component, $instanceName );
		$this->PushStringData( $component, $instanceName, $viewName );
		$this->PushModelStack();

		$this->view->RenderAsHTML();
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
	}

	private function PushHierarchy( $component, $instanceName )
	{
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