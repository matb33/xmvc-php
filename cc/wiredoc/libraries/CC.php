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

use xMVC\Mod\Language\Language;

class CC
{
	private static $eventPump = null;

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

	public static function RegisterNamespaces( &$model )
	{
		foreach( Config::$data[ "ccNamespaces" ] as $prefix => $namespace )
		{
			$model->xPath->registerNamespace( $prefix, $namespace );
		}
	}

	public static function InjectReferences( &$model )
	{
		self::RegisterNamespaces( $model );
		self::InjectNextReference( $model );
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
		$instance = $node->getAttribute( "name" );

		$modelName = StringUtils::ReplaceTokensInPattern( Config::$data[ "componentInstanceFilePattern" ], array( "component" => $component, "instance" => $instance ) );
		$instanceModel = new XMLModelDriver( $modelName );

		self::InjectModel( $instanceModel, $node, $model );
		self::InjectNextReference( $model );
	}

	private static function InjectModel( $instanceModel, $node, &$model )
	{
		self::RegisterNamespaces( $instanceModel );

		$originalNode = $node->cloneNode( true );

		$externalNode = $model->importNode( $instanceModel->xPath->query( "//component:*" )->item( 0 ), true );
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
		$cache = ( int )$node->getAttribute( "cache" );

		$arguments = array();
		$arguments[ "component" ] = $component;
		$arguments[ "node" ] = $node;
		$arguments[ "model" ] = $model;
		$arguments[ "instanceName" ] = $instanceName;
		$arguments[ "eventName" ] = $eventName;
		$arguments[ "cache" ] = $cache;
		$arguments[ "inject" ] = true;

		while( $node->hasAttribute( "param" . ( ++$i ) ) )
		{
			$arguments[ "param" ][ $i ] = $node->getAttribute( "param" . $i );
		}

		$arguments[ "cacheid" ] = $component . $instanceName . $eventName . implode( "", $arguments[ "param" ] );

		self::StartBuildingComponent( $eventName, $arguments );
	}

	public static function RenderComponent( $component, $eventName, $instanceName, $dispatchScope, $parameters = array(), $cache = 0 )
	{
		$delegate = new Delegate( "OnComponentInstanceGenerated", $dispatchScope );

		self::GenerateComponentInstance( $component, $eventName, $instanceName, $delegate, $parameters, $cache );
	}

	public static function GenerateComponentInstance( $component, $eventName, $instanceName, $delegate, $parameters = array(), $cache = 0 )
	{
		$cache = ( int )$cache;

		$arguments = array();
		$arguments[ "component" ] = $component;
		$arguments[ "instanceName" ] = $instanceName;
		$arguments[ "eventName" ] = $eventName;
		$arguments[ "cache" ] = $cache;
		$arguments[ "inject" ] = false;

		foreach( $parameters as $i => $parameter )
		{
			$arguments[ "param" ][ $i + 1 ] = $parameter;
		}

		$arguments[ "cacheid" ] = $component . $instanceName . $eventName . implode( "", $arguments[ "param" ] );

		self::GetEventPump()->addEventListener( "oncomponentinstancebuilt", $delegate );
		self::StartBuildingComponent( $eventName, $arguments );
	}

	private static function StartBuildingComponent( $eventName, $arguments )
	{
		$arguments[ "cacheFile" ] = self::GetCacheFilename( $eventName, $arguments[ "cache" ], $arguments[ "cacheid" ] );

		if( $arguments[ "cache" ] == 0 || !file_exists( $arguments[ "cacheFile" ] ) )
		{
			self::GetEventPump()->dispatchEvent( new Event( $eventName, $arguments ) );
		}
		else
		{
			$arguments[ "cachedResultModel" ] = new XMLModelDriver( $arguments[ "cacheFile" ] );
			self::Talk( null, new Event( $eventName, $arguments ) );
		}
	}

	private static function GetCacheFilename( $eventName, $cache, $cacheid )
	{
		$cacheFile = null;

		if( $cache > 0 )
		{
			$hash = $cacheid . "-" . md5( $cacheid . floor( time() / ( $cache * 60 ) ) );
			$cacheFile = StringUtils::ReplaceTokensInPattern( Config::$data[ "componentCacheFilePattern" ], array( "event" => $eventName, "hash" => $hash ) );
		}

		return( $cacheFile );
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

			CC::GetEventPump()->dispatchEvent( new Event( "oncomponentinstancebuilt", array( "model" => $resultModel, "component" => $event->arguments[ "data" ][ "component" ], "instanceName" => $event->arguments[ "data" ][ "instanceName" ] ) ) );
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
			self::CacheResultModel( $event->arguments[ "data" ][ "cache" ], $event->arguments[ "data" ][ "cacheFile" ], $event->arguments[ "data" ][ "cacheid" ], $resultModel );
		}

		return( $resultModel );
	}

	private static function CacheResultModel( $cache, $cacheFile, $cacheid, $resultModel )
	{
		if( $cache > 0 )
		{
			if( Cache::PrepCacheFolder( $cacheFile, $cacheid . "-*" ) )
			{
				file_put_contents( $cacheFile, $resultModel->saveXML() );
			}
		}
	}

	private static function TransformBuiltComponentToInstance( Event $event )
	{
		$component = $event->arguments[ "data" ][ "component" ];
		$instanceName = $event->arguments[ "data" ][ "instanceName" ];
		$eventName = $event->arguments[ "data" ][ "eventName" ];
		$cache = $event->arguments[ "data" ][ "cache" ];
		$sourceModel = $event->arguments[ "sourceModel" ];

		$view = new View();
		$view->SetXSLData( $view->ImportXSL( null, StringUtils::ReplaceTokensInPattern( Config::$data[ "componentFilePattern" ], array( "component" => $component ) ) ) );
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

	public static function Listen( $eventName, Delegate $delegate )
	{
		self::GetEventPump()->addEventListener( $eventName, $delegate );
	}

	public static function Talk( $sourceModel, Event &$event )
	{
		self::GetEventPump()->dispatchEvent( new Event( "oncomponentbuildcomplete", array( "sourceModel" => $sourceModel, "data" => $event->arguments ) ) );
	}

	public static function InjectRSSFeed( $model )
	{
		self::RegisterNamespaces( $model );

		foreach( $model->xPath->query( "//inject:rss-feed" ) as $node )
		{
			$rssModel = new XMLModelDriver( $node->getAttribute( "url" ) );
			$rssNodes = $rssModel->xPath->query( "//rss" );

			if( $rssNodes->length > 0 )
			{
				$node->setAttribute( "xmlns", "http://www.w3.org/2005/Atom" );

				$rssNode = $rssNodes->item( 0 );
				$newNode = $model->importNode( $rssNode, true );
				$node->appendChild( $newNode );
			}
		}

		return( $model );
	}

	public static function InjectStrings( $model, $stringModel )
	{
		self::RegisterNamespaces( $model );

		foreach( $model->xPath->query( "//inject:get-string" ) as $stringNode )
		{
			$currentKey = $stringNode->getAttribute( "name" );
			$targetStringNode = $stringModel->xPath->query( "//xmvc:" . $currentKey );

			if( $targetStringNode->length > 0 )
			{
				$textNode = $model->createTextNode( $targetStringNode->item( 0 )->nodeValue );
				$stringNode->parentNode->replaceChild( $textNode, $stringNode );
			}
		}

		return( $model );
	}

	public static function InjectLang( &$view, $lang )
	{
		$models = $view->GetModels();

		foreach( $models as $model )
		{
			self::RegisterNamespaces( $model );

			foreach( $model->xPath->query( "//*[ @inject:lang != '' ]" ) as $itemNode )
			{
				$attributeName = $itemNode->getAttribute( "inject:lang" );

				$langNode = $model->createAttribute( $attributeName );
				$langNode->value = $lang;
				$itemNode->appendChild( $langNode );

				$itemNode->removeAttribute( "inject:lang" );
			}
		}
	}

	public static function InjectHref( &$view )
	{
		$models = $view->GetModels();

		foreach( $models as $model )
		{
			self::RegisterNamespaces( $model );

			foreach( $model->xPath->query( "//*[ @inject:href != '' ]" ) as $itemNode )
			{
				$pageName = $itemNode->getAttribute( "inject:href" );
				$prefix = $itemNode->hasAttribute( "inject:href-prefix" ) ? $itemNode->getAttribute( "inject:href-prefix" ) : "";
				$suffix = $itemNode->hasAttribute( "inject:href-suffix" ) ? $itemNode->getAttribute( "inject:href-suffix" ) : "";

				$path = Sitemap::GetPathByPageNameAndLanguage( $pageName, Language::GetLang() );

				$linkNode = $model->createAttribute( "href" );
				$linkNode->value = $prefix . $path . $suffix;
				$itemNode->appendChild( $linkNode );

				$itemNode->removeAttribute( "inject:href" );
			}
		}
	}

	public static function InjectLinkNextToLangSwap( &$view )
	{
		$currentPageName = Sitemap::GetCurrentPageName();

		$models = $view->GetModels();

		foreach( $models as $model )
		{
			self::RegisterNamespaces( $model );

			foreach( $model->xPath->query( "//*[ inject:lang-swap != '' ]" ) as $itemNode )
			{
				$langSwapNode = $model->xPath->query( "inject:lang-swap[ @lang = '" . Language::GetLang() . "' ]", $itemNode )->item( 0 );
				$targetLang = $langSwapNode->nodeValue;
				$suffix = $langSwapNode->getAttribute( "suffix" );

				$path = Sitemap::GetPathByPageNameAndLanguage( $currentPageName, $targetLang ) . $suffix;

				$linkNode = $model->createElementNS( Config::$data[ "ccNamespaces" ][ "link" ], "link:href", $path );
				$itemNode->appendChild( $linkNode );
			}
		}
	}

	public static function InjectMathCaptcha( $model )
	{
		self::RegisterNamespaces( $model );

		foreach( $model->xPath->query( "//inject:math-captcha" ) as $mathCaptchaNode )
		{
			$type = $mathCaptchaNode->getAttribute( "type" );

			switch( $type )
			{
				case "subtraction":
					$formula = rand( 1, 9 ) . " - " . rand( 1, 9 );
				break;

				case "multiplication":
					$formula = rand( 1, 9 ) . " x " . rand( 1, 9 );
				break;

				case "division":
					$formula = rand( 1, 9 ) . " / " . rand( 1, 9 );
				break;

				case "addition":
				default:
					$formula = rand( 1, 9 ) . " + " . rand( 1, 9 );
			}

			eval( "\$answer = (" . str_replace( "x", "*", $formula ) . ");" );

			$answerAttribute = $model->createAttribute( "answer" );
			$answerAttribute->value = $answer;
			$mathCaptchaNode->appendChild( $answerAttribute );

			$answerMD5Attribute = $model->createAttribute( "answer-md5" );
			$answerMD5Attribute->value = md5( $answer );
			$mathCaptchaNode->appendChild( $answerMD5Attribute );

			$formulaTextNode = $model->createTextNode( $formula );
			$mathCaptchaNode->appendChild( $formulaTextNode );
		}

		return( $model );
	}
}

?>