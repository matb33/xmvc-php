<?php

namespace xMVC\Mod\CC;

use xMVC\Sys\Loader;
use xMVC\Sys\Core;
use xMVC\Sys\XMLModelDriver;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\View;
use xMVC\Sys\Routing;
use xMVC\Sys\Config;
use xMVC\Sys\Events\DefaultEventDispatcher;
use xMVC\Sys\Events\Event;
use xMVC\Sys\Delegate;
use xMVC\Sys\Filesystem;
use xMVC\Sys\Normalize;

use xMVC\Mod\Language\Language;
use xMVC\Mod\Utils\DOMUtils;
use xMVC\Mod\Utils\StringUtils;

class CC
{
	private static $eventPump = null;

	public static function Listen( $eventName, Delegate $delegate )
	{
		self::GetEventPump()->addEventListener( $eventName, $delegate );
	}

	public static function Talk( $sourceModel, Event &$event )
	{
		self::GetEventPump()->dispatchEvent( new Event( "oncomponentbuildcomplete", array( "sourceModel" => $sourceModel, "data" => $event->arguments ) ) );
	}

	public static function GetEventPump()
	{
		if( is_null( self::$eventPump ) )
		{
			self::InitializeEventHandling();
		}

		return( self::$eventPump );
	}

	private static function InitializeEventHandling()
	{
		self::$eventPump = new DefaultEventDispatcher();
		self::$eventPump->addEventListener( "oncomponentbuildcomplete", new Delegate( "\\xMVC\\Mod\\CC\\CC::OnComponentBuildComplete" ) );
	}

	public static function RenderComponent( $component, $eventName, $instanceName, $dispatchScope, $parameters = array(), $cacheMinutes = 0 )
	{
		$delegate = new Delegate( "OnComponentInstanceGenerated", $dispatchScope );

		self::GenerateComponentInstance( $component, $eventName, $instanceName, $delegate, $parameters, $cacheMinutes );
	}

	public static function RenderInstance( $component, $instanceName, $dispatchScope, $cacheMinutes = 0 )
	{
		$delegate = new Delegate( "OnComponentInstanceGenerated", $dispatchScope );
		$instanceModel = self::LoadComponentInstance( $component, $instanceName, $cacheMinutes );

		self::GetEventPump()->addEventListener( "oncomponentinstancebuilt", $delegate );
		self::GetEventPump()->dispatchEvent( new Event( "oncomponentinstancebuilt", array( "model" => $instanceModel, "component" => $component, "instanceName" => $instanceName ) ) );
	}

	public static function InjectReferences( &$model )
	{
		self::RegisterNamespaces( $model );
		self::InjectNextReference( $model );
	}

	public static function RegisterNamespaces( &$model )
	{
		foreach( Config::$data[ "ccNamespaces" ] as $prefix => $namespace )
		{
			$model->xPath->registerNamespace( $prefix, $namespace );
		}
	}

	private static function InjectNextReference( &$model )
	{
		$references = $model->xPath->query( "//reference:*[ local-name() = 'instance' or local-name() = 'component' ]" );

		if( $references->length > 0 )
		{
			$node = $references->item( 0 );

			switch( $node->localName )
			{
				case "instance":
					self::InjectInstanceReference( $node, $model );
				break;
				case "component":
					self::InjectComponentReference( $node, $model );
				break;
			}
		}
	}

	private static function InjectInstanceReference( $node, &$model )
	{
		$component = $node->getAttribute( "component" );
		$instanceName = $node->getAttribute( "name" );
		$cacheMinutes = $node->hasAttribute( "cache" ) ? ( int )$node->getAttribute( "cache" ) : 0;

		$instanceModel = self::LoadComponentInstance( $component, $instanceName, $cacheMinutes );

		self::InjectModel( $instanceModel, $node, $model );
		self::InjectNextReference( $model );
	}

	private static function LoadComponentInstance( $component, $instanceName, $cacheMinutes = 0 )
	{
		$modelName = StringUtils::ReplaceTokensInPattern( Config::$data[ "componentInstanceFilePattern" ], array( "component" => $component, "instance" => $instanceName ) );

		$cacheID = self::GenerateCacheID( $component, $instanceName );
		$cache = new Cache( Config::$data[ "componentCacheFilePattern" ], array( "type" => "instances", "name" => $cacheID ), $cacheID, true, $cacheMinutes );

		if( $cache->IsCached() )
		{
			$instanceModel = $cache->Read();
		}
		else
		{
			$instanceModel = new XMLModelDriver( $modelName );
			$cache->Write( $instanceModel );
		}

		return( $instanceModel );
	}

	private static function InjectModel( $instanceModel, $node, &$model )
	{
		self::RegisterNamespaces( $instanceModel );

		$originalNode = $node->cloneNode( true );

		$externalNode = $model->importNode( $instanceModel->xPath->query( "//component:definition" )->item( 0 ), true );
		$node->parentNode->replaceChild( $externalNode, $node );

		$childRefNodeList = $model->xPath->query( "//reference:child", $node );

		if( $childRefNodeList->length > 0 )
		{
			$childRefNode = $childRefNodeList->item( 0 );
			$importedNode = $model->importNode( $originalNode, true );
			DOMUtils::ReplaceNodeWithChildren( $childRefNode, $importedNode );
		}
	}

	private static function InjectComponentReference( $node, &$model )
	{
		$component = $node->getAttribute( "name" );
		$instanceName = $node->getAttribute( "instance-name" );
		$eventName = $node->getAttribute( "event" );
		$cacheMinutes = ( int )$node->getAttribute( "cache" );

		$arguments = array();
		$arguments[ "component" ] = $component;
		$arguments[ "node" ] = $node;
		$arguments[ "model" ] = $model;
		$arguments[ "instanceName" ] = $instanceName;
		$arguments[ "eventName" ] = $eventName;
		$arguments[ "cacheMinutes" ] = $cacheMinutes;
		$arguments[ "inject" ] = true;
		$arguments[ "param" ] = array();

		$i = 0;

		while( $node->hasAttribute( "param" . ( ++$i ) ) )
		{
			$arguments[ "param" ][ $i ] = $node->getAttribute( "param" . $i );
		}

		$arguments[ "cacheID" ] = self::GenerateCacheID( $component, $instanceName, $eventName, $arguments );

		self::StartBuildingComponent( $eventName, $arguments );
	}

	private static function StartBuildingComponent( $eventName, $arguments )
	{
		$arguments[ "cache" ] = new Cache( Config::$data[ "componentCacheFilePattern" ], array( "type" => "events", "name" => $eventName ), $arguments[ "cacheID" ], true, $arguments[ "cacheMinutes" ] );

		if( $arguments[ "cache" ]->IsCached() )
		{
			$arguments[ "cachedResultModel" ] = $arguments[ "cache" ]->Read();
			self::Talk( null, new Event( $eventName, $arguments ) );
		}
		else
		{
			self::GetEventPump()->dispatchEvent( new Event( $eventName, $arguments ) );
		}
	}

	public static function GenerateComponentInstance( $component, $eventName, $instanceName, $delegate, $parameters = array(), $cacheMinutes = 0 )
	{
		$cacheMinutes = ( int )$cacheMinutes;

		$arguments = array();
		$arguments[ "component" ] = $component;
		$arguments[ "instanceName" ] = $instanceName;
		$arguments[ "eventName" ] = $eventName;
		$arguments[ "cacheMinutes" ] = $cacheMinutes;
		$arguments[ "inject" ] = false;
		$arguments[ "param" ] = array();

		foreach( $parameters as $i => $parameter )
		{
			$arguments[ "param" ][ $i + 1 ] = $parameter;
		}

		$arguments[ "cacheID" ] = self::GenerateCacheID( $component, $instanceName, $eventName, $arguments );

		self::GetEventPump()->addEventListener( "oncomponentinstancebuilt", $delegate );
		self::StartBuildingComponent( $eventName, $arguments );
	}

	private static function GenerateCacheID( $component, $instanceName, $eventName = null, $arguments = null )
	{
		$cacheID = str_replace( "\\", "_", $component ) . "_" . $instanceName;

		if( !is_null( $eventName ) )
		{
			$cacheID .= "_" . $eventName;
		}

		if( !is_null( $arguments ) && isset( $arguments[ "param" ] ) )
		{
			$cacheID .= "_" . implode( "", $arguments[ "param" ] );
		}

		return( $cacheID );
	}

	public static function OnComponentBuildComplete( Event $event )
	{
		if( $event->arguments[ "data" ][ "inject" ] )
		{
			self::InjectBuiltComponent( $event );
		}
		else
		{
			$resultModel = self::ObtainResultModel( $event );

			self::GetEventPump()->dispatchEvent( new Event( "oncomponentinstancebuilt", array( "model" => $resultModel, "component" => $event->arguments[ "data" ][ "component" ], "instanceName" => $event->arguments[ "data" ][ "instanceName" ] ) ) );
		}
	}

	private static function InjectBuiltComponent( Event $event )
	{
		$resultModel = self::ObtainResultModel( $event );

		$node = $event->arguments[ "data" ][ "node" ];
		$model = $event->arguments[ "data" ][ "model" ];

		self::InjectModel( $resultModel, $node, $model );
		self::InjectNextReference( $model );
	}

	private static function ObtainResultModel( Event $event )
	{
		if( isset( $event->arguments[ "data" ][ "cachedResultModel" ] ) )
		{
			$resultModel = $event->arguments[ "data" ][ "cachedResultModel" ];
		}
		else
		{
			$resultModel = self::TransformBuiltComponentToInstance( $event );
			$event->arguments[ "data" ][ "cache" ]->Write( $resultModel );
		}

		self::ConsultSitemapWithInstance( $resultModel );

		return( $resultModel );
	}

	private static function TransformBuiltComponentToInstance( Event $event )
	{
		$component = $event->arguments[ "data" ][ "component" ];
		$instanceName = $event->arguments[ "data" ][ "instanceName" ];
		$eventName = $event->arguments[ "data" ][ "eventName" ];
		$cacheMinutes = $event->arguments[ "data" ][ "cacheMinutes" ];
		$sourceModel = $event->arguments[ "sourceModel" ];
		$componentOnly = array_pop( explode( "\\", $component ) );

		$xslFile = Normalize::Filename( StringUtils::ReplaceTokensInPattern( Config::$data[ "componentFilePattern" ], array( "component" => $component, "component-only" => $componentOnly ) ) );

		$view = new View();
		$view->SetXSLData( $view->ImportXSL( null, $xslFile ) );
		$view->PushModel( $sourceModel );
		$result = new \DOMDocument();
		$result->loadXML( $view->ProcessAsXML() );

		if( !is_null( $instanceName ) && strlen( $instanceName ) > 0 )
		{
			if( !$result->documentElement->hasAttribute( "instance-name" ) )
			{
				$nameAttribute = $result->createAttribute( "instance-name" );
				$nameAttribute->value = $instanceName;
				$result->documentElement->appendChild( $nameAttribute );
			}
		}

		$injectLangAttribute = $result->createAttributeNS( Config::$data[ "ccNamespaces" ][ "inject" ], "inject:lang" );
		$injectLangAttribute->value = "xml:lang";
		$result->documentElement->appendChild( $injectLangAttribute );

		$resultXML = $result->saveXML();
		$resultModel = new XMLModelDriver( $resultXML );

		return( $resultModel );
	}

	private static function ConsultSitemapWithInstance( $model )
	{
		if( $model->xPath->query( "//meta:href" )->length > 0 )
		{
			$metaDataCollectionByLang = Sitemap::GetMetaData( $model );

			if( !Sitemap::MetaDataAlreadyPresent( $metaDataCollectionByLang, Language::GetLang() ) )
			{
				Sitemap::AddMetaDataCollectionByLangToSitemap( $metaDataCollectionByLang );
			}
		}
	}
}

?>