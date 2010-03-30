<?php

namespace Module\CC;

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

use Module\Language\Language;

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
		self::$eventPump->addEventListener( "oncomponentbuildcomplete", new Delegate( "\\Module\\CC\\CC::OnComponentBuildComplete" ) );
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

		$modelName = Core::namespaceApp . "instances/" . $component . "/" . $instance . ".xml";
		$instanceModel = new XMLModelDriver( $modelName );

		self::InjectModel( $instanceModel, $node, $model );
		self::InjectNextReference( $model );
	}

	private static function InjectModel( $instanceModel, $node, &$model )
	{
		self::RegisterNamespaces( $instanceModel );

		$originalNode = $node->cloneNode( true );

		$externalNode = $model->importNode( $instanceModel->xPath->query( "//instance:*" )->item( 0 ), true );
		$node->parentNode->replaceChild( $externalNode, $node );

		$childRefNodeList = $model->xPath->query( "//reference:child", $node );

		if( $childRefNodeList->length > 0 )
		{
			$childRefNode = $childRefNodeList->item( 0 );
			$importedNode = $model->importNode( $originalNode, true );
			self::ReplaceNodeWithChildren( $childRefNode, $importedNode );
		}
	}

	private static function ReplaceNodeWithChildren( &$refNode, &$node )
	{
		for( $i = 0; $i < $node->childNodes->length; $i++ )
		{
			$childNode = $node->childNodes->item( $i );

			if( !( $childNode instanceof \DOMText ) )
			{
				$refNode->parentNode->insertBefore( $childNode, $refNode );
			}
		}

		$refNode->parentNode->removeChild( $refNode );
	}

	private static function InjectComponentReference( $node, &$model )
	{
		$component = $node->getAttribute( "name" );
		$eventName = $node->getAttribute( "event" );
		$instanceName = $node->getAttribute( "instance-name" );

		$arguments = array( "component" => $component, "node" => $node, "model" => $model, "instanceName" => $instanceName, "inject" => true );

		while( $node->hasAttribute( "param" . ( ++$i ) ) )
		{
			$arguments[ "param" ][ $i ] = $node->getAttribute( "param" . $i );
		}

		self::GetEventPump()->dispatchEvent( new Event( $eventName, $arguments ) );
	}

	public static function RenderComponent( $component, $eventName, $instanceName, $scope, $parameters = array() )
	{
		$delegate = new Delegate( "OnComponentInstanceGenerated", $scope );

		self::GenerateComponentInstance( $component, $eventName, $instanceName, $delegate, $parameters );
	}

	public static function GenerateComponentInstance( $component, $eventName, $instanceName, $delegate, $parameters = array() )
	{
		$arguments = array( "component" => $component, "instanceName" => $instanceName, "inject" => false );

		foreach( $parameters as $i => $parameter )
		{
			$arguments[ "param" ][ $i + 1 ] = $parameter;
		}

		self::GetEventPump()->addEventListener( "oncomponentinstancebuilt", $delegate );
		self::GetEventPump()->dispatchEvent( new Event( $eventName, $arguments ) );
	}

	public static function OnComponentBuildComplete( Event $event )
	{
		if( $event->arguments[ "data" ][ "inject" ] )
		{
			self::InjectBuiltComponent( $event );
		}
		else
		{
			$model = self::TransformBuiltComponentToInstance( $event );

			CC::GetEventPump()->dispatchEvent( new Event( "oncomponentinstancebuilt", array( "model" => $model, "component" => $event->arguments[ "data" ][ "component" ], "instanceName" => $event->arguments[ "data" ][ "instanceName" ] ) ) );
		}
	}

	private static function InjectBuiltComponent( Event $event )
	{
		$node = $event->arguments[ "data" ][ "node" ];
		$model = $event->arguments[ "data" ][ "model" ];

		$resultModel = self::TransformBuiltComponentToInstance( $event );

		self::InjectModel( $resultModel, $node, $model );
		self::InjectNextReference( $model );
	}

	private static function TransformBuiltComponentToInstance( Event $event )
	{
		$component = $event->arguments[ "data" ][ "component" ];
		$instanceName = $event->arguments[ "data" ][ "instanceName" ];
		$sourceModel = $event->arguments[ "sourceModel" ];

		$view = new View( "components/" . $component );
		$view->PushModel( $sourceModel );
		$result = new \DOMDocument();
		$result->loadXML( $view->ProcessAsXML() );

		if( ! is_null( $instanceName ) && strlen( $instanceName ) > 0 )
		{
			$nameAttribute = $result->createAttribute( "name" );
			$nameAttribute->value = $instanceName;
			$result->documentElement->appendChild( $nameAttribute );
		}

		$injectLangAttribute = $result->createAttributeNS( Config::$data[ "ccNamespaces" ][ "inject" ], "inject:lang" );
		$injectLangAttribute->value = "xml:lang";
		$result->documentElement->appendChild( $injectLangAttribute );

		$resultModel = new XMLModelDriver( $result->saveXML() );

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

	// Should evolve this function to work with any model driver
	public static function GetCachedXMLModelDriver( $modelName, Event &$event, $updateCacheEveryXMinutes = 1440 )
	{
		$cacheFolder = "app/inc/cache/" .  $event->type . "/";
		$cacheFile = md5( $modelName . floor( time() / ( $updateCacheEveryXMinutes * 60 ) ) ) . ".xml";

		if( file_exists( $cacheFolder . $cacheFile ) )
		{
			$model = new XMLModelDriver( $cacheFolder . $cacheFile );
		}
		else
		{
			$model = new XMLModelDriver( $modelName );

			FileSystem::CreateFolderStructure( $cacheFolder );

			if( FileSystem::TestPermissions( $cacheFolder, FileSystem::FS_PERM_WRITE ) )
			{
				file_put_contents( $cacheFolder . $cacheFile, $model->saveXML() );
			}
		}

		return( $model );
	}
}

?>