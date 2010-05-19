<?php

namespace xMVC\Mod\WireKit\Components;

use xMVC\Sys\Delegate;
use xMVC\Sys\Events\Event;
use xMVC\Sys\Loader;
use xMVC\Sys\Config;
use xMVC\Sys\Events\DefaultEventDispatcher;
use xMVC\Mod\Utils\DOMUtils;
use xMVC\Mod\Language\Language;

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

		return( true );
	}

	public function OnComponentReadyForExpansion( Event $event )
	{
		$componentModel = $event->arguments[ "model" ];

		ComponentUtils::RegisterNamespaces( $componentModel );
		ComponentUtils::CreateDefinitionAttributeIfMissing( $componentModel, "name", $event->arguments[ "component" ] );
		ComponentUtils::CreateDefinitionAttributeIfMissing( $componentModel, "instance-name", $event->arguments[ "instanceName" ] );
		ComponentUtils::CreateDefinitionAttributeIfMissing( $componentModel, "wd:name", $event->arguments[ "component" ] . "." . $event->arguments[ "instanceName" ] );

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
		$references = $this->rootModel->xPath->query( "//reference:component | //wd:reference" );

		if( $references->length > 0 )
		{
			$this->referenceNode = $references->item( 0 );

			if( $this->referenceNode->hasAttribute( "wd:name" ) )
			{
				// Wiredoc 2.0
				list( $component, $instanceName, $null ) = ComponentUtils::ExtractComponentNamePartsFromWiredocName( $this->referenceNode->getAttribute( "wd:name" ) );
			}
			else
			{
				// Wiredoc 1.0
				$component = $this->referenceNode->getAttribute( "name" );
				$instanceName = $this->referenceNode->getAttribute( "instance-name" );
			}

			$eventName = $this->referenceNode->getAttribute( "event" );
			$cacheMinutes = ( int )$this->referenceNode->getAttribute( "cache" );

			$i = 0;
			$parameters = array();

			while( $this->referenceNode->hasAttribute( "param" . ( ++$i ) ) )
			{
				$parameters[ $i - 1 ] = $this->referenceNode->getAttribute( "param" . $i );
			}

			return( $this->GetComponent( $component, $instanceName, $eventName, $parameters, $cacheMinutes ) );
		}

		return( false );
	}

	private function InjectComponentModel( $componentModel )
	{
		$originalNode = $this->referenceNode->cloneNode( true );

		$externalNode = $this->rootModel->importNode( $componentModel->xPath->query( "//component:definition | //wd:component" )->item( 0 ), true );
		$this->referenceNode->parentNode->replaceChild( $externalNode, $this->referenceNode );

		$childRefNodeList = $this->rootModel->xPath->query( "//reference:parent-children | //wd:parent-children", $this->referenceNode );

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
		foreach( $this->rootModel->xPath->query( "//*[ @inject:href or @wd:inject-href ]" ) as $itemNode )
		{
			if( $itemNode->hasAttribute( "wd:inject-href" ) )
			{
				// Wiredoc 2.0
				$fullyQualifiedName = $itemNode->getAttribute( "wd:inject-href" );
				$prefix = $itemNode->hasAttribute( "wd:inject-href-prefix" ) ? $itemNode->getAttribute( "wd:inject-href-prefix" ) : "";
				$suffix = $itemNode->hasAttribute( "wd:inject-href-suffix" ) ? $itemNode->getAttribute( "wd:inject-href-suffix" ) : "";
				$targetLang = $itemNode->hasAttribute( "wd:inject-href-lang" ) ? $itemNode->getAttribute( "wd:inject-href-lang" ) : Language::GetLang();

				$itemNode->removeAttribute( "wd:inject-href" );
				$itemNode->removeAttribute( "wd:inject-href-prefix" );
				$itemNode->removeAttribute( "wd:inject-href-suffix" );
				$itemNode->removeAttribute( "wd:inject-href-lang" );
			}
			else
			{
				// Wiredoc 1.0
				$fullyQualifiedName = $itemNode->getAttribute( "inject:href" );
				$prefix = $itemNode->hasAttribute( "inject:href-prefix" ) ? $itemNode->getAttribute( "inject:href-prefix" ) : "";
				$suffix = $itemNode->hasAttribute( "inject:href-suffix" ) ? $itemNode->getAttribute( "inject:href-suffix" ) : "";
				$targetLang = $itemNode->hasAttribute( "inject:href-lang" ) ? $itemNode->getAttribute( "inject:href-lang" ) : Language::GetLang();

				$itemNode->removeAttribute( "inject:href" );
			}

			if( strlen( $fullyQualifiedName ) == 0 )
			{
				$fullyQualifiedName = implode( ".", ComponentUtils::GetHrefContextComponentAndInstanceName( $this->rootModel ) );
			}

			$path = ComponentLookup::getInstance()->GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $targetLang );

			$linkNode = $this->rootModel->createAttribute( "href" );
			$linkNode->value = $prefix . $path . $suffix;
			$itemNode->appendChild( $linkNode );
		}
	}

	private function InjectLang( $lang )
	{
		foreach( $this->rootModel->xPath->query( "//*[ @inject:lang != '' or @wd:inject-lang != '' ]" ) as $itemNode )
		{
			if( $itemNode->hasAttribute( "wd:inject-lang" ) )
			{
				// Wiredoc 2.0
				$attributeName = $itemNode->getAttribute( "wd:inject-lang" );
				$itemNode->removeAttribute( "wd:inject-lang" );
			}
			else
			{
				// Wiredoc 1.0
				$attributeName = $itemNode->getAttribute( "inject:lang" );
				$itemNode->removeAttribute( "inject:lang" );
			}

			$langNode = $this->rootModel->createAttribute( $attributeName );
			$langNode->value = $lang;
			$itemNode->appendChild( $langNode );
		}
	}

	private function ResetFactory()
	{
		$this->rootModel = null;
		$this->referenceModel = null;
	}
}

?>