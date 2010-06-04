<?php

namespace xMVC\Mod\WireKit\Components;

use xMVC\Sys\Events\DefaultEventDispatcher;
use xMVC\Sys\Delegate;
use xMVC\Sys\Events\Event;
use xMVC\Sys\Config;
use xMVC\Sys\Routing;
use xMVC\Sys\Loader;
use xMVC\Sys\Normalize;
use xMVC\Sys\View;
use xMVC\Sys\XMLModelDriver;
use xMVC\Mod\WireKit\Cache;
use xMVC\Mod\Utils\StringUtils;

abstract class Component extends DefaultEventDispatcher
{
	private $component = null;
	private $instanceName = null;
	private $fullyQualifiedName = null;
	private $eventName = null;
	private $parameters = array();
	private $methodName = null;
	private $methodArgs = array();
	private $builtComponentModel = null;
	private $cacheMinutes = 0;
	private $cacheID = null;
	private $cache = null;
	private $cachedResultModel = null;
	private $componentClass = null;
	private $componentModelName = null;

	public function __construct( $componentClass, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 )
	{
		$cacheMinutes = ( int )$cacheMinutes;
		$eventName = ComponentUtils::DefaultEventNameIfNecessary( $eventName );
		$wiredocComponentName = ComponentUtils::ExtractWiredocComponentNameFromComponentClass( $componentClass );

		$pathParts = Routing::GetPathParts();
		list( $this->component, $this->instanceName, $this->fullyQualifiedName ) = ComponentUtils::ExtractComponentNamePartsFromWiredocName( $wiredocComponentName . "." . $instanceName );
		$this->componentClass = $componentClass;
		$this->componentModelName = substr( $componentClass, 0, strrpos( $componentClass, "\\" ) + 1 ) . $instanceName;
		$this->eventName = $eventName;
		$this->parameters = $parameters;
		$this->cacheMinutes = $cacheMinutes;
		$this->methodName = Normalize::MethodOrClassName( $pathParts[ 1 ] );
		$this->methodArgs = array_slice( $pathParts, 2 );
		$this->cacheID = $this->GenerateCacheID();

		$this->addEventListener( "ontalk.components", new Delegate( "OnTalk", $this ) );
		$this->addEventListener( "default.components", new Delegate( "OnDefault", $this ) );
	}

	public function Build()
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

		if( $this->cache->IsCached() )
		{
			$this->cachedResultModel = $this->cache->Read();
			$this->Talk( null, $buildEvent );
		}
		else
		{
			$this->dispatchEvent( $buildEvent );
		}
	}

	private function GenerateCacheID()
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

	protected function Listen( $eventName, Delegate $delegate )
	{
		$this->addEventListener( $eventName, $delegate );
	}

	protected function Talk( $builtComponentModel )
	{
		$this->dispatchEvent( new Event( "ontalk.components", array( "builtComponentModel" => $builtComponentModel ) ) );
	}

	public function OnTalk( Event $event )
	{
		$this->builtComponentModel = $event->arguments[ "builtComponentModel" ];
		$this->SendResultModelForProcessing( $this->ObtainResultModel() );
	}

	public function OnDefault( Event $event )
	{
		$this->SendResultModelForProcessing( $this->LoadComponentInstance() );
	}

	private function LoadComponentInstance()
	{
		$modelName = $this->componentModelName;

		$cacheID = self::GenerateCacheID();
		$cache = new Cache( Config::$data[ "componentCacheFilePattern" ], array( "type" => "instances", "name" => $this->cacheID ), $this->cacheID, true, $this->cacheMinutes );

		if( $cache->IsCached() )
		{
			$instanceModel = $cache->Read();
		}
		else
		{
			$instanceModel = new XMLModelDriver( $modelName );

			if( $this->cacheMinutes > 0 )
			{
				$cache->Write( $instanceModel );
			}
		}

		return $instanceModel;
	}

	private function ObtainResultModel()
	{
		if( isset( $this->cachedResultModel ) )
		{
			$resultModel = $this->cachedResultModel;
		}
		else
		{
			$resultModel = $this->TransformBuiltComponentToInstance();

			if( $this->cacheMinutes > 0 )
			{
				$this->cache->Write( $resultModel );
			}
		}

		ComponentLookup::getInstance()->EnsureInstanceInLookup( $resultModel );

		return $resultModel;
	}

	private function TransformBuiltComponentToInstance()
	{
		$component = $this->component;
		$instanceName = $this->instanceName;
		$builtComponentModel = $this->builtComponentModel;

		$componentClass = ComponentUtils::GetComponentClassNameFromWiredocComponentName( $component );
		$namespacedComponentClass = ComponentUtils::DefaultNamespaceIfNecessary( $componentClass );

		$xslFile = Loader::Resolve( "components", $namespacedComponentClass, "xsl" );

		$view = new View();
		$xslData = $view->ImportXSL( null, $xslFile );
		$view->SetXSLData( $xslData );
		$view->PushModel( $builtComponentModel );
		$result = new \DOMDocument();
		$resultXML = $view->ProcessAsXML();
		$result->loadXML( $resultXML );

		if( !is_null( $instanceName ) && strlen( $instanceName ) > 0 )
		{
			if( !is_null( $result->documentElement ) )
			{
				if( !$result->documentElement->hasAttribute( "wd:name" ) )
				{
					$nameAttribute = $result->createAttributeNS( Config::$data[ "wirekitNamespaces" ][ "wd" ], "wd:name" );
					$nameAttribute->value = $component . "." . $instanceName;
					$result->documentElement->appendChild( $nameAttribute );
				}
			}
			else
			{
				$this->OnComponentTransformationError( $view, $xslData, $xslFile, $resultXML );
			}
		}

		$injectLangAttribute = $result->createAttributeNS( Config::$data[ "wirekitNamespaces" ][ "meta" ], "meta:inject-lang" );
		$injectLangAttribute->value = "xml:lang";
		$result->documentElement->appendChild( $injectLangAttribute );

		$resultXML = $result->saveXML();
		$resultModel = new XMLModelDriver( $resultXML );

		return $resultModel;
	}

	private function SendResultModelForProcessing( $resultModel )
	{
		ComponentUtils::ReplaceTokenParametersInAttributes( $resultModel, $this->parameters );

		$arguments = array(
			"model" => $resultModel,
			"component" => $this->component,
			"instanceName" => $this->instanceName
		);

		$this->dispatchEvent( new Event( "onreadyforprocessing.components", $arguments ) );
	}

	private function OnComponentTransformationError( $view, $xslData, $xslFile, $resultXML )
	{
		$stackModel = new XMLModelDriver( $view->GetStackedModels() );
		$stackModel->dump( false, $this->component . "\\" . $this->instanceName  );

		$viewModel = new XMLModelDriver( $xslData );
		$viewModel->dump( false, $xslFile );

		echo( "<pre>" . htmlentities( $resultXML ) . "</pre>" );

		trigger_error( "There was an unspecified error while tranforming the model above using the XSL component also displayed above. Finally displayed is the resulting XML causing this error", E_USER_ERROR );
	}
}