<?php

namespace Modules\WiredocPHP\Libraries\Components;

use System\Libraries\Delegate;
use System\Libraries\Events\Event;
use System\Libraries\Loader;
use System\Libraries\Config;
use System\Libraries\Events\DefaultEventDispatcher;
use System\Libraries\Routing;
use System\Libraries\Normalize;
use Modules\Utils\Libraries\DOMUtils;
use Modules\Language\Libraries\Language;

class ComponentFactory extends DefaultEventDispatcher
{
	private $rootModel;
	private $rootFullyQualifiedName;
	private $referenceNode;

	public function __construct()
	{
		$this->resetFactory();
	}

	public function getComponent( $component, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 )
	{
		$componentClass = ComponentUtils::getComponentClassNameFromWiredocComponentName( $component );
		$eventName = ComponentUtils::defaultEventNameIfNecessary( $eventName );
		$namespacedComponentClass = ComponentUtils::defaultNamespaceIfNecessary( $componentClass );

		if( Loader::resolve( Component::componentFolder, $namespacedComponentClass, Component::componentExtension ) )
		{
			$instance = new $namespacedComponentClass( null, $instanceName, $eventName, $parameters, $cacheMinutes );
		}
		else
		{
			$instance = new GenericComponent( $namespacedComponentClass, $instanceName, $eventName, $parameters, $cacheMinutes );
		}

		$instance->addEventListener( "onreadyforprocessing.components", new Delegate( "onComponentReadyForExpansion", $this ) );
		$instance->build();

		return true;
	}

	public function onComponentReadyForExpansion( Event $event )
	{
		$componentModel = $event->arguments[ "model" ];

		ComponentUtils::registerNamespaces( $componentModel );
		ComponentUtils::createDefinitionAttributeIfMissing( $componentModel, Config::$data[ "wiredocNamespaces" ][ "wd" ], "wd:name", $event->arguments[ "component" ] . "." . $event->arguments[ "instanceName" ] );

		$isRootModel = is_null( $this->rootModel );
		$isInjecting = !is_null( $this->referenceNode );

		if( $isRootModel )
		{
			$this->rootModel = $componentModel;
			$this->rootFullyQualifiedName = $event->arguments[ "component" ] . "." . $event->arguments[ "instanceName" ];
		}

		if( $isInjecting )
		{
			$this->injectComponentModel( $componentModel );
		}

		while( $this->expandNextReference() !== false );

		if( $isRootModel )
		{
			$this->injectHref();
			$this->injectLang( Language::getLang() );
			$this->resetFactory();

			$this->dispatchEvent( new Event( "onreadyforrender.components", $event->arguments ) );
		}
	}

	private function expandNextReference()
	{
		$references = $this->rootModel->xPath->query( "//wd:reference" );

		if( $references->length > 0 )
		{
			$this->referenceNode = $references->item( 0 );

			list( $component, $instanceName, $null ) = ComponentUtils::extractComponentNamePartsFromWiredocName( $this->referenceNode->getAttribute( "wd:name" ) );

			$eventName = $this->referenceNode->getAttribute( "wd:event" );
			$cacheMinutes = ( int )$this->referenceNode->getAttribute( "wd:cache" );

			$i = 0;
			$parameters = array();

			while( $this->referenceNode->hasAttribute( "wd:param" . ( ++$i ) ) )
			{
				$parameters[ $i - 1 ] = $this->referenceNode->getAttribute( "wd:param" . $i );
			}

			return $this->getComponent( $component, $instanceName, $eventName, $parameters, $cacheMinutes );
		}

		return false;
	}

	private function injectComponentModel( $componentModel )
	{
		$originalNode = $this->referenceNode->cloneNode( true );

		$externalNode = $this->rootModel->importNode( $componentModel->xPath->query( "//wd:component" )->item( 0 ), true );
		$this->referenceNode->parentNode->replaceChild( $externalNode, $this->referenceNode );

		$childRefNodeList = $this->rootModel->xPath->query( "//wd:parent-children", $this->referenceNode );

		for( $i = 0; $i < $childRefNodeList->length; $i++ )
		{
			$childRefNode = $childRefNodeList->item( $i );
			$importedNode = $this->rootModel->importNode( $originalNode, true );
			DOMUtils::replaceNodeWithChildren( $childRefNode, $importedNode );
		}

		$this->referenceNode = null;
	}

	private function injectHref()
	{
		$itemNodeList = $this->rootModel->xPath->query( "//*[ @meta:inject-href ]" );

		foreach( $itemNodeList as $itemNode )
		{
			$fullyQualifiedName = $itemNode->getAttribute( "meta:inject-href" );
			$prefix = $itemNode->hasAttribute( "meta:inject-href-prefix" ) ? $itemNode->getAttribute( "meta:inject-href-prefix" ) : "";
			$suffix = $itemNode->hasAttribute( "meta:inject-href-suffix" ) ? $itemNode->getAttribute( "meta:inject-href-suffix" ) : "";
			$targetLang = $itemNode->hasAttribute( "meta:inject-href-lang" ) ? $itemNode->getAttribute( "meta:inject-href-lang" ) : Language::getLang();

			$itemNode->removeAttribute( "meta:inject-href" );
			$itemNode->removeAttribute( "meta:inject-href-prefix" );
			$itemNode->removeAttribute( "meta:inject-href-suffix" );
			$itemNode->removeAttribute( "meta:inject-href-lang" );

			if( strlen( $fullyQualifiedName ) == 0 )
			{
				$fullyQualifiedName = $this->rootFullyQualifiedName;
			}

			$path = ComponentLookup::getInstance()->GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $targetLang );

			$linkNode = $this->rootModel->createAttribute( "href" );
			$linkNode->value = $prefix . $path . $suffix;
			$itemNode->appendChild( $linkNode );
		}
	}

	private function injectLang( $lang )
	{
		$itemNodeList = $this->rootModel->xPath->query( "//*[ @meta:inject-lang or @meta:inject-lang-base or @meta:inject-lang-locale ]" );

		foreach( $itemNodeList as $itemNode )
		{
			if( $itemNode->hasAttribute( "meta:inject-lang" ) )
			{
				$attributeName = $itemNode->getAttribute( "meta:inject-lang" );
				$itemNode->removeAttribute( "meta:inject-lang" );
				$langValueToInsert = $lang;
			}
			elseif( $itemNode->hasAttribute( "meta:inject-lang-base" ) )
			{
				$attributeName = $itemNode->getAttribute( "meta:inject-lang-base" );
				$itemNode->removeAttribute( "meta:inject-lang-base" );
				$langValueToInsert = Language::getLangBase( $lang );
			}
			elseif( $itemNode->hasAttribute( "meta:inject-lang-locale" ) )
			{
				$attributeName = $itemNode->getAttribute( "meta:inject-lang-locale" );
				$itemNode->removeAttribute( "meta:inject-lang-locale" );
				$langValueToInsert = Language::getLangLocale( $lang );
			}

			$langNode = $this->rootModel->createAttribute( $attributeName );
			$langNode->value = $langValueToInsert;
			$itemNode->appendChild( $langNode );
		}
	}

	private function resetFactory()
	{
		$this->rootModel = null;
		$this->referenceModel = null;
	}
}