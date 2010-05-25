<?php

namespace xMVC\Mod\WireKit\Components;

use xMVC\Sys\Loader;
use xMVC\Sys\Config;
use xMVC\Sys\Routing;
use xMVC\Sys\Normalize;

class ComponentUtils
{
	public static function RegisterNamespaces( &$model )
	{
		foreach( Config::$data[ "wirekitNamespaces" ] as $prefix => $namespace )
		{
			$model->xPath->registerNamespace( $prefix, $namespace );
		}
	}

	public static function GetComponentClassNameFromWiredocComponentName( $wiredocComponentName )
	{
		list( $component, $null, $null ) = self::ExtractComponentNamePartsFromWiredocName( $wiredocComponentName . "." );

		$componentClass = str_replace( "/", "\\", $component );
		
		if( strpos( $componentClass, "\\" ) !== false )
		{
			$componentClass = $componentClass . strrchr( $componentClass, "\\" );
		}
		else
		{
			$componentClass = $componentClass . "\\" . $componentClass;
		}

		return( $componentClass );
	}

	public static function DefaultEventNameIfNecessary( $eventName )
	{
		if( is_null( $eventName ) || strlen( $eventName ) == 0 )
		{
			return( "default.components" );
		}

		return( $eventName );
	}

	public static function DefaultNamespaceIfNecessary( $componentClass )
	{
		if( strpos( $componentClass, Config::$data[ "componentNamespace" ] ) === false )
		{
			return( Config::$data[ "componentNamespace" ] . "\\" . $componentClass );
		}

		return( $componentClass );
	}

	public static function FallbackViewNameIfNecessary( $viewName )
	{
		if( strlen( trim( $viewName ) ) == 0 )
		{
			$viewName = Config::$data[ "defaultView" ];
		}

		return( $viewName );
	}

	public static function ReplaceTokenParametersInAttributes( &$model, $parameters )
	{
		foreach( $model->xPath->query( "//@*[ contains( name(), 'param' ) and contains( ., '#param' ) ]" ) as $paramNode )
		{
			$paramNode->nodeValue = $parameters[ substr( $paramNode->nodeValue, 6, -1 ) - 1 ];
		}
	}

	public static function GetHrefContextComponentAndInstanceName( $model )
	{
		// Href Context refers to the component that holds the meta:href matchig the current URI

		$hrefContextComponent = "";
		$hrefContextInstanceName = "";

		$currentHref = Normalize::StripQueryInURI( Routing::URI() );
		$hrefNodeList = $model->xPath->query( "//meta:href[ .= '" . $currentHref . "' ]" );

		if( $hrefNodeList->length > 0 )
		{
			$componentDefinitionNodeList = $model->xPath->query( "ancestor::component:definition[1] | ancestor::wd:component[1]", $hrefNodeList->item( $hrefNodeList->length - 1 ) );

			if( $componentDefinitionNodeList->length > 0 )
			{
				$componentDefinitionNode = $componentDefinitionNodeList->item( 0 );

				if( $componentDefinitionNode->hasAttribute( "name" ) )
				{
					// Wiredoc 1.0
					$hrefContextComponent = $componentDefinitionNode->hasAttribute( "name" ) ? $componentDefinitionNode->getAttribute( "name" ) : "";
					$hrefContextInstanceName = $componentDefinitionNode->hasAttribute( "instance-name" ) ? $componentDefinitionNode->getAttribute( "instance-name" ) : "";
				}
				else
				{
					// Wiredoc 2.0
					list( $hrefContextComponent, $hrefContextInstanceName, $hrefContextFullyQualifiedName ) = ComponentUtils::ExtractComponentNamePartsFromWiredocName( $componentDefinitionNode->nodeValue );
				}
			}
		}

		return( array( $hrefContextComponent, $hrefContextInstanceName ) );
	}

	public static function CreateDefinitionAttributeIfMissing( $model, $name, $value )
	{
		$definitionNodeList = $model->xPath->query( "//component:definition | //wd:component" );

		if( $definitionNodeList->length > 0 )
		{
			$definitionNode = $definitionNodeList->item( 0 );

			if( !$definitionNode->hasAttribute( $name ) )
			{
				$attribute = $model->createAttribute( $name );
				$attribute->value = $value;
				$definitionNode->appendChild( $attribute );
			}
		}
	}

	public static function ExtractWiredocComponentNameFromComponentClass( $componentClass )
	{
		$nonNamespacedComponentClass = str_replace( Config::$data[ "componentNamespace" ] . "\\", "", $componentClass );
		$componentName = substr( $nonNamespacedComponentClass, 0, strrpos( $nonNamespacedComponentClass, "\\" ) );

		return( str_replace( "\\", "/", $componentName ) );
	}

	public static function ExtractComponentNamePartsFromWiredocName( $fullyQualifiedNameWiredocName )
	{
		$component = substr( $fullyQualifiedNameWiredocName, 0, strrpos( $fullyQualifiedNameWiredocName, "." ) );
		$instanceName = substr( strrchr( $fullyQualifiedNameWiredocName, "." ), 1 );

		$fullyQualifiedName = $fullyQualifiedNameWiredocName;

		return( array( $component, $instanceName, $fullyQualifiedName ) );
	}
}