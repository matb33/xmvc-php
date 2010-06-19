<?php

namespace Module\WebWiredoc\Libraries\Components;

use System\Libraries\Delegate;
use System\Libraries\Events\Event;
use System\Libraries\Loader;
use System\Libraries\Config;
use System\Libraries\Events\DefaultEventDispatcher;
use System\Libraries\Routing;
use System\Libraries\Normalize;
use Module\Utils\Libraries\DOMUtils;
use Module\Language\Libraries\Language;

class ComponentFactory extends DefaultEventDispatcher
{
	private $rootModel;
	private $referenceNode;

	public function __construct()
	{
		$this->ResetFactory();
	}

	public function GetComponent( $component, $instanceName = null, $eventName = null, $parameters = array(), $cacheMinutes = 0 )
	{
		$componentClass = ComponentUtils::GetComponentClassNameFromWiredocComponentName( $component );
		$eventName = ComponentUtils::DefaultEventNameIfNecessary( $eventName );
		$namespacedComponentClass = ComponentUtils::DefaultNamespaceIfNecessary( $componentClass );

		if( class_exists( $namespacedComponentClass, true ) )
		{
			$instance = new $namespacedComponentClass( null, $instanceName, $eventName, $parameters, $cacheMinutes );
		}
		else
		{
			$instance = new GenericComponent( $namespacedComponentClass, $instanceName, $eventName, $parameters, $cacheMinutes );
		}

		$instance->addEventListener( "onreadyforprocessing.components", new Delegate( "OnComponentReadyForExpansion", $this ) );
		$instance->Build();

		return true;
	}

	public function OnComponentReadyForExpansion( Event $event )
	{
		$componentModel = $event->arguments[ "model" ];

		ComponentUtils::RegisterNamespaces( $componentModel );
		ComponentUtils::CreateDefinitionAttributeIfMissing( $componentModel, Config::$data[ "wiredocNamespaces" ][ "wd" ], "wd:name", $event->arguments[ "component" ] . "." . $event->arguments[ "instanceName" ] );

		$isRootModel = is_null( $this->rootModel );
		$isInjecting = !is_null( $this->referenceNode );

		if( $isRootModel )
		{
			$this->rootModel = $componentModel;
		}

		if( $isInjecting )
		{
			$this->InjectComponentModel( $componentModel );
		}

		while( $this->ExpandNextReference() !== false );

		if( $isRootModel )
		{
			$this->InjectHref();
			$this->InjectLang( Language::GetLang() );
			$this->ResetFactory();
			$this->dispatchEvent( new Event( "onreadyforrender.components", $event->arguments ) );
		}
	}

	private function ExpandNextReference()
	{
		$references = $this->rootModel->xPath->query( "//wd:reference" );

		if( $references->length > 0 )
		{
			$this->referenceNode = $references->item( 0 );

			list( $component, $instanceName, $null ) = ComponentUtils::ExtractComponentNamePartsFromWiredocName( $this->referenceNode->getAttribute( "wd:name" ) );

			$eventName = $this->referenceNode->getAttribute( "wd:event" );
			$cacheMinutes = ( int )$this->referenceNode->getAttribute( "wd:cache" );

			$i = 0;
			$parameters = array();

			while( $this->referenceNode->hasAttribute( "wd:param" . ( ++$i ) ) )
			{
				$parameters[ $i - 1 ] = $this->referenceNode->getAttribute( "wd:param" . $i );
			}

			return $this->GetComponent( $component, $instanceName, $eventName, $parameters, $cacheMinutes );
		}

		return false;
	}

	private function InjectComponentModel( $componentModel )
	{
		$originalNode = $this->referenceNode->cloneNode( true );

		$externalNode = $this->rootModel->importNode( $componentModel->xPath->query( "//wd:component" )->item( 0 ), true );
		$this->referenceNode->parentNode->replaceChild( $externalNode, $this->referenceNode );

		$childRefNodeList = $this->rootModel->xPath->query( "//wd:parent-children", $this->referenceNode );

		for( $i = 0; $i < $childRefNodeList->length; $i++ )
		{
			$childRefNode = $childRefNodeList->item( $i );
			$importedNode = $this->rootModel->importNode( $originalNode, true );
			DOMUtils::ReplaceNodeWithChildren( $childRefNode, $importedNode );
		}

		$this->referenceNode = null;
	}

	private function InjectHref()
	{
		foreach( $this->rootModel->xPath->query( "//*[ @meta:inject-href ]" ) as $itemNode )
		{
			$fullyQualifiedName = $itemNode->getAttribute( "meta:inject-href" );
			$prefix = $itemNode->hasAttribute( "meta:inject-href-prefix" ) ? $itemNode->getAttribute( "meta:inject-href-prefix" ) : "";
			$suffix = $itemNode->hasAttribute( "meta:inject-href-suffix" ) ? $itemNode->getAttribute( "meta:inject-href-suffix" ) : "";
			$targetLang = $itemNode->hasAttribute( "meta:inject-href-lang" ) ? $itemNode->getAttribute( "meta:inject-href-lang" ) : Language::GetLang();

			$itemNode->removeAttribute( "meta:inject-href" );
			$itemNode->removeAttribute( "meta:inject-href-prefix" );
			$itemNode->removeAttribute( "meta:inject-href-suffix" );
			$itemNode->removeAttribute( "meta:inject-href-lang" );

			if( strlen( $fullyQualifiedName ) == 0 )
			{
				$path = Normalize::StripQueryInURI( Routing::URI() );
			}
			else
			{
				$path = ComponentLookup::getInstance()->GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $targetLang );
			}

			$linkNode = $this->rootModel->createAttribute( "href" );
			$linkNode->value = $prefix . $path . $suffix;
			$itemNode->appendChild( $linkNode );
		}
	}

	private function InjectLang( $lang )
	{
		foreach( $this->rootModel->xPath->query( "//*[ @meta:inject-lang or @meta:inject-lang-base or @meta:inject-lang-locale ]" ) as $itemNode )
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
				$langValueToInsert = Language::GetLangBase( $lang );
			}
			elseif( $itemNode->hasAttribute( "meta:inject-lang-locale" ) )
			{
				$attributeName = $itemNode->getAttribute( "meta:inject-lang-locale" );
				$itemNode->removeAttribute( "meta:inject-lang-locale" );
				$langValueToInsert = Language::GetLangLocale( $lang );
			}

			$langNode = $this->rootModel->createAttribute( $attributeName );
			$langNode->value = $langValueToInsert;
			$itemNode->appendChild( $langNode );
		}
	}

	private function ResetFactory()
	{
		$this->rootModel = null;
		$this->referenceModel = null;
	}
}