<?php

namespace Modules\WiredocPHP\Controllers;

use System\Libraries\Core;
use System\Libraries\Routing;
use System\Libraries\Loader;
use System\Libraries\Config;
use System\Libraries\ErrorHandler;
use System\Drivers\XMLModelDriver;
use System\Drivers\StringsModelDriver;
use System\Libraries\View;
use System\Libraries\Normalize;
use System\Libraries\Delegate;
use System\Libraries\Events\Event;
use System\Libraries\Events\DefaultEventDispatcher;

use Modules\Language\Libraries\Language;
use Modules\Utils\Libraries\StringUtils;
use Modules\WiredocPHP\Libraries\Components\ComponentLookup;
use Modules\WiredocPHP\Libraries\Components\ComponentFactory;
use Modules\WiredocPHP\Libraries\Components\ComponentUtils;
use Modules\WiredocPHP\Drivers\HierarchyModelDriver;

class Processor
{
	private $application;
	private $view;
	private $modelStack;

	public function __construct()
	{
		$this->modelStack = array();
		$this->application = new Config::$data[ "applicationClass" ]( $this->modelStack );

		if( Config::$data[ "isLocal" ] || ComponentLookup::getInstance()->hostsDontMatch() )
		{
			ComponentLookup::getInstance()->generate();
		}
	}

	protected function call()
	{
		$pathParts = $this->getPathParts();

		call_user_func_array( "self::Page", $pathParts );
	}

	protected function getPathParts()
	{
		$pathParts = Routing::getPathParts();
		$pathParts[ 0 ] = Loader::stripNamespace( $pathParts[ 0 ] );

		return $pathParts;
	}

	public function page()
	{
		$args = func_get_args();

		if( count( $args ) )
		{
			$isFile = strpos( $args[ count( $args ) - 1 ], "." ) !== false;
			$currentPath = "/" . implode( "/", $args ) . ( $isFile ? "" : "/" );
		}
		else
		{
			$currentPath = "/";
		}

		if( ( $componentData = ComponentLookup::getInstance()->getComponentDataByPath( $currentPath ) ) !== false )
		{
			$this->renderPageUsingComponentData( $componentData );
		}
		else
		{
			$this->invoke404( $currentPath );
		}
	}

	public function renderComponent( $wiredocName, $eventName = null, $parameters = array(), $delegate = null, $cacheMinutes = 0 )
	{
		$fullyQualifiedWiredocName = ComponentUtils::fullyQualifyWiredocName( $wiredocName );

		if( ( $componentData = ComponentLookup::getInstance()->getComponentDataByFullyQualifiedName( $fullyQualifiedWiredocName ) ) !== false )
		{
			$componentData[ "eventName" ] = $eventName;
			$componentData[ "parameters" ] = $parameters;
			$componentData[ "cacheMinutes" ] = $cacheMinutes;

			$this->renderPageUsingComponentData( $componentData, $delegate );
		}
		else
		{
			$this->invoke404( $fullyQualifiedWiredocName );
		}
	}

	public function renderPageUsingComponentData( $componentData, $delegate = null )
	{
		$component = $componentData[ "component" ];
		$instanceName = isset( $componentData[ "instanceName" ] ) ? $componentData[ "instanceName" ] : null;
		$eventName = isset( $componentData[ "eventName" ] ) ? $componentData[ "eventName" ] : null;
		$parameters = isset( $componentData[ "parameters" ] ) ? $componentData[ "parameters" ] : array();
		$cacheMinutes = isset( $componentData[ "cacheMinutes" ] ) ? $componentData[ "cacheMinutes" ] : 0;
		$matchingLang = isset( $componentData[ "matchingLang" ] ) && strlen( $componentData[ "matchingLang" ] ) > 0 ? $componentData[ "matchingLang" ] : null;

		if( ! is_null( $matchingLang ) )
		{
			Language::setLang( $matchingLang );
		}

		if( is_null( $delegate ) )
		{
			$delegate = new Delegate( "onComponentReadyForRender", $this );
		}

		$factory = new ComponentFactory();
		$factory->addEventListener( "onreadyforrender.components", $delegate );
		$factory->getComponent( $component, $instanceName, $eventName, $parameters, $cacheMinutes );
	}

	public function onComponentReadyForRender( Event $event )
	{
		$model = $event->arguments[ "model" ];
		$component = $event->arguments[ "component" ];
		$instanceName = $event->arguments[ "instanceName" ];

		$this->renderPageWithModel( $model, $component, $instanceName );
	}

	private function invoke404( $path )
	{
		ErrorHandler::invokeHTTPError( array( "errorCode" => "404", "controllerFile" => __CLASS__, "method" => $path ) );
	}

	public function renderPageWithModel( $model, $component, $instanceName )
	{
		$viewNameNodeList = $model->xPath->query( "//meta:view[ last() ]" );
		$viewName = $viewNameNodeList->length > 0 ? $viewNameNodeList->item( 0 )->nodeValue : "";
		$viewName = ComponentUtils::fallbackViewNameIfNecessary( $viewName );

		$this->view = new View( $viewName );
		$this->view->pushModel( $model );

		list( $hrefContextComponent, $hrefContextInstanceName, $hrefContextFullyQualifiedName, $currentHref ) = ComponentUtils::getHrefContextComponentAndInstanceName( $model );
		$this->pushHierarchy( $hrefContextComponent, $hrefContextInstanceName, $hrefContextFullyQualifiedName, $currentHref );
		$this->renderPage( $component, $instanceName, $viewName );
	}

	private function renderPage( $component, $instanceName, $viewName )
	{
		$this->pushXLIFF( $component, $instanceName );
		$this->pushStringData( $component, $instanceName, $viewName );
		$this->pushModelStack();

		$this->view->renderAsHTML( null, null, 3600 );
	}

	private function pushXLIFF( $component, $instanceName )
	{
		$filename = StringUtils::replaceTokensInPattern( Config::$data[ "xliffFilePattern" ], array( "component" => $component, "instance" => $instanceName ) );
		$pathInfo = pathinfo( $filename );

		if( XMLModelDriver::exists( $pathInfo[ "dirname" ] . "/" . $pathInfo[ "filename" ], $pathInfo[ "extension" ] ) )
		{
			$xliffModel = new XMLModelDriver( $filename );
			$this->view->pushModel( $xliffModel );
		}
	}

	private function pushStringData( $component, $instanceName, $viewName )
	{
		$basePath = Routing::URIProtocol() . "://" . $_SERVER[ "HTTP_HOST" ];
		$uri = Normalize::stripQueryInURI( Routing::URI() );
		$link = $basePath . $uri;

		$stringData = new StringsModelDriver();
		$stringData->add( "lang", Language::getLang() );
		$stringData->add( "component", $component );
		$stringData->add( "instance", $instanceName );
		$stringData->add( "instance-file", $instanceName . "." . Loader::modelExtension );
		$stringData->add( "view-name", $viewName );
		$stringData->add( "base-path", $basePath );
		$stringData->add( "http-host", $_SERVER[ "HTTP_HOST" ] );
		$stringData->add( "uri", $uri );
		$stringData->add( "link", $link );
		$stringData->add( "link-urlencoded", urlencode( $link ) );

		$slashCount = substr_count( $uri, "/" ) - 1;

		if( $slashCount <= 0 )
		{
			$relativePathModifier = ".";
		}
		else
		{
			$relativePathModifier = implode( "/", array_fill( 0, $slashCount, ".." ) );
		}

		$stringData->add( "relative-path-modifier", $relativePathModifier );

		if( isset( Config::$data[ "isProduction" ] ) && Config::$data[ "isProduction" ] )
		{
			$stringData->add( "cache-buster", md5( $_SERVER[ "HTTP_HOST" ] ) );
		}
		else
		{
			$stringData->add( "cache-buster", md5( date( "Y-m-d H:i:s" ) . rand( 0, 9999 ) ) );
		}

		$this->view->pushModel( $stringData );
	}

	private function pushHierarchy( $component, $instanceName, $fullyQualifiedName, $currentHref )
	{
		$this->view->pushModel( new HierarchyModelDriver( $component, $instanceName, $fullyQualifiedName, $currentHref ) );
	}

	private function pushModelStack()
	{
		foreach( $this->modelStack as $model )
		{
			$this->view->pushModel( $model );
		}
	}
}