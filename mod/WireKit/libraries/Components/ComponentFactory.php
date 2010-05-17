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
		$componentClass = ComponentUtils::GetComponentClassName( $component );
		$eventName = ComponentUtils::DefaultEventNameIfNecessary( $eventName );

		if( class_exists( $componentClass, true ) )
		{
			$instance = new $componentClass( null, $instanceName, $eventName, $parameters, $cacheMinutes );
		}
		else
		{
			$instance = new GenericComponent( $componentClass, $instanceName, $eventName, $parameters, $cacheMinutes );
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
		$references = $this->rootModel->xPath->query( "//reference:component" );

		if( $references->length > 0 )
		{
			$this->referenceNode = $references->item( 0 );

			$component = $this->referenceNode->getAttribute( "name" );
			$instanceName = $this->referenceNode->getAttribute( "instance-name" );
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

		$externalNode = $this->rootModel->importNode( $componentModel->xPath->query( "//component:definition" )->item( 0 ), true );
		$this->referenceNode->parentNode->replaceChild( $externalNode, $this->referenceNode );

		$childRefNodeList = $this->rootModel->xPath->query( "//reference:parent-children", $this->referenceNode );

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
		foreach( $this->rootModel->xPath->query( "//*[ @inject:href ]" ) as $itemNode )
		{
			$fullyQualifiedName = $itemNode->getAttribute( "inject:href" );
			$prefix = $itemNode->hasAttribute( "inject:href-prefix" ) ? $itemNode->getAttribute( "inject:href-prefix" ) : "";
			$suffix = $itemNode->hasAttribute( "inject:href-suffix" ) ? $itemNode->getAttribute( "inject:href-suffix" ) : "";
			$targetLang = $itemNode->hasAttribute( "inject:href-lang" ) ? $itemNode->getAttribute( "inject:href-lang" ) : Language::GetLang();

			if( strlen( $fullyQualifiedName ) == 0 )
			{
				$fullyQualifiedName = implode( "\\", ComponentUtils::GetHrefContextComponentAndInstanceName( $this->rootModel ) );
			}

			$path = ComponentLookup::getInstance()->GetPathByFullyQualifiedNameAndLanguage( $fullyQualifiedName, $targetLang );

			$linkNode = $this->rootModel->createAttribute( "href" );
			$linkNode->value = $prefix . $path . $suffix;
			$itemNode->appendChild( $linkNode );

			$itemNode->removeAttribute( "inject:href" );
		}
	}

	private function InjectLang( $lang )
	{
		foreach( $this->rootModel->xPath->query( "//*[ @inject:lang != '' ]" ) as $itemNode )
		{
			$attributeName = $itemNode->getAttribute( "inject:lang" );

			$langNode = $this->rootModel->createAttribute( $attributeName );
			$langNode->value = $lang;
			$itemNode->appendChild( $langNode );

			$itemNode->removeAttribute( "inject:lang" );
		}
	}

	private function ResetFactory()
	{
		$this->rootModel = null;
		$this->referenceModel = null;
	}
}

?>