<?php

namespace Modules\WiredocPHP\Libraries\Components;

use System\Libraries\Events\DefaultEventDispatcher;
use System\Libraries\Delegate;
use System\Libraries\Events\Event;
use System\Libraries\Config;
use System\Libraries\Routing;
use System\Libraries\Loader;
use System\Libraries\Normalize;
use System\Libraries\View;
use System\Drivers\XMLModelDriver;
use Modules\Cache\Libraries\Cache;
use Modules\Utils\Libraries\StringUtils;

abstract class Component extends DefaultEventDispatcher
{
	const componentExtension = "xsl";
	const componentFolder = "Components";

	private $component = null;
	private $instanceName = null;
	private $fullyQualifiedName = null;
	private $eventName = null;
	private $parameters = array();
	private $methodName = null;
	private $methodArgs = array();
	private $builtComponentModels = null;
	private $cacheMinutes = 0;
	private $cacheID = null;
	private $cache = null;
	private $cachedResultModel = null;
	private $componentClass = null;
	private $componentModelName = null;

	public function __construct( $componentClass, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 )
	{
		$cacheMinutes = ( int )$cacheMinutes;
		$eventName = ComponentUtils::defaultEventNameIfNecessary( $eventName );
		$wiredocComponentName = ComponentUtils::extractWiredocComponentNameFromComponentClass( $componentClass );

		$pathParts = Routing::getPathParts();
		list( $this->component, $this->instanceName, $this->fullyQualifiedName ) = ComponentUtils::extractComponentNamePartsFromWiredocName( $wiredocComponentName . "." . $instanceName );
		$this->componentClass = $componentClass;
		$this->componentModelName = substr( $componentClass, 0, strrpos( $componentClass, "\\" ) + 1 ) . $instanceName;
		$this->eventName = $eventName;
		$this->parameters = $parameters;
		$this->cacheMinutes = $cacheMinutes;
		$this->methodName = Normalize::methodName( $pathParts[ 1 ] );
		$this->methodArgs = array_slice( $pathParts, 2 );
		$this->cacheID = $this->generateCacheID();

		$this->addEventListener( "ontalk.components", new Delegate( "onTalk", $this ) );
		$this->addEventListener( "default.components", new Delegate( "onDefault", $this ) );
	}

	public function build()
	{
		$arguments = array();
		$arguments[ "component" ] = $this->component;
		$arguments[ "instanceName" ] = $this->instanceName;
		$arguments[ "componentClass" ] = $this->componentClass;
		$arguments[ "componentModelName" ] = $this->componentModelName;
		$arguments[ "eventName" ] = $this->eventName;
		$arguments[ "param" ] = $this->parameters;
		$arguments[ "methodName" ] = $this->methodName;
		$arguments[ "methodArgs" ] = $this->methodArgs;

		$buildEvent = new Event( $this->eventName, $arguments );
		$this->cache = new Cache( Config::$data[ "componentCacheFilePattern" ], array( "type" => "events", "name" => $this->eventName ), $this->cacheID, true, $this->cacheMinutes );

		if( $this->cache->isCached() )
		{
			$this->cachedResultModel = $this->cache->read();
			$this->talk( null, $buildEvent );
		}
		else
		{
			$this->dispatchEvent( $buildEvent );
		}
	}

	private function generateCacheID()
	{
		$cacheID = str_replace( "\\", "_", $this->componentModelName );

		if( ! is_null( $this->eventName ) )
		{
			$cacheID .= "_" . $this->eventName;
		}

		if( is_array( $this->parameters ) && count( $this->parameters ) )
		{
			$cacheID .= "_" . implode( "", $this->parameters );
		}

		return $cacheID;
	}

	protected function listen( $eventName, Delegate $delegate )
	{
		$this->addEventListener( $eventName, $delegate );
	}

	protected function talk()
	{
		$this->dispatchEvent( new Event( "ontalk.components", array( "builtComponentModels" => func_get_args() ) ) );
	}

	public function onTalk( Event $event )
	{
		$this->builtComponentModels = $event->arguments[ "builtComponentModels" ];
		$this->sendResultModelForProcessing( $this->obtainResultModel() );
	}

	public function onDefault( Event $event )
	{
		$this->sendResultModelForProcessing( $this->loadComponentInstance() );
	}

	private function loadComponentInstance()
	{
		$modelName = $this->componentModelName;

		$cacheID = self::generateCacheID();
		$cache = new Cache( Config::$data[ "componentCacheFilePattern" ], array( "type" => "instances", "name" => $this->cacheID ), $this->cacheID, true, $this->cacheMinutes );

		if( $cache->isCached() )
		{
			$instanceModel = $cache->read();
		}
		else
		{
			$instanceModel = new XMLModelDriver( $modelName );

			if( $this->cacheMinutes > 0 )
			{
				$cache->write( $instanceModel );
			}
		}

		return $instanceModel;
	}

	private function obtainResultModel()
	{
		if( isset( $this->cachedResultModel ) )
		{
			$resultModel = $this->cachedResultModel;
		}
		else
		{
			$resultModel = $this->transformBuiltComponentToInstance();

			if( $this->cacheMinutes > 0 )
			{
				$this->cache->write( $resultModel );
			}
		}

		ComponentLookup::getInstance()->ensureInstanceInLookup( $resultModel );

		return $resultModel;
	}

	private function transformBuiltComponentToInstance()
	{
		$component = $this->component;
		$instanceName = $this->instanceName;
		$builtComponentModels = $this->builtComponentModels;

		$componentClass = ComponentUtils::getComponentClassNameFromWiredocComponentName( $component );
		$namespacedComponentClass = ComponentUtils::defaultNamespaceIfNecessary( $componentClass );

		$view = new View();
		$xslFile = Loader::resolve( self::componentFolder, $namespacedComponentClass, self::componentExtension );
		$xslData = $view->importXSL( null, $xslFile );
		$view->setXSLData( $xslData );

		foreach( $builtComponentModels as $model )
		{
			$view->pushModel( $model );
		}

		$resultXML = $view->processAsXML();
		$resultModel = new XMLModelDriver( $resultXML );

		if( !is_null( $instanceName ) && strlen( $instanceName ) > 0 )
		{
			if( !is_null( $resultModel->documentElement->firstChild ) )
			{
				if( !$resultModel->documentElement->firstChild->hasAttribute( "wd:name" ) )
				{
					$nameAttribute = $resultModel->createAttributeNS( Config::$data[ "wiredocNamespaces" ][ "wd" ], "wd:name" );
					$nameAttribute->value = $component . "." . $instanceName;
					$resultModel->documentElement->firstChild->appendChild( $nameAttribute );
				}
			}
			else
			{
				$this->onComponentTransformationError( $view, $xslData, $xslFile, $resultXML );
			}
		}

		$injectLangAttribute = $resultModel->createAttributeNS( Config::$data[ "wiredocNamespaces" ][ "meta" ], "meta:inject-lang" );
		$injectLangAttribute->value = "xml:lang";
		$resultModel->documentElement->firstChild->appendChild( $injectLangAttribute );

		return $resultModel;
	}

	private function sendResultModelForProcessing( $resultModel )
	{
		ComponentUtils::replaceTokenParametersInAttributes( $resultModel, $this->parameters );

		$arguments = array(
			"model" => $resultModel,
			"component" => $this->component,
			"instanceName" => $this->instanceName
		);

		$this->dispatchEvent( new Event( "onreadyforprocessing.components", $arguments ) );
	}

	private function onComponentTransformationError( $view, $xslData, $xslFile, $resultXML )
	{
		$stackModel = new XMLModelDriver( $view->getAggregatedModels() );
		$stackModel->dump( false, $this->component . "\\" . $this->instanceName  );

		$viewModel = new XMLModelDriver( $xslData );
		$viewModel->dump( false, $xslFile );

		echo( "<pre>" . htmlentities( $resultXML ) . "</pre>" );

		trigger_error( "There was an unspecified error while tranforming the model above using the XSL component also displayed above. Finally displayed is the resulting XML causing this error", E_USER_ERROR );
	}
}