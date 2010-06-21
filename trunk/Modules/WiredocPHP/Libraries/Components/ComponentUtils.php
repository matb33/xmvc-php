<?php

namespace Modules\WiredocPHP\Libraries\Components;

use System\Libraries\Loader;
use System\Libraries\Config;
use System\Libraries\Routing;
use System\Libraries\Normalize;

class ComponentUtils
{
	public static function RegisterNamespaces( &$model )
	{
		foreach( Config::$data[ "wiredocNamespaces" ] as $prefix => $namespace )
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

		return $componentClass;
	}

	public static function DefaultEventNameIfNecessary( $eventName )
	{
		if( is_null( $eventName ) || strlen( $eventName ) == 0 )
		{
			return "default.components";
		}

		return $eventName;
	}

	public static function DefaultNamespaceIfNecessary( $componentClass )
	{
		if( strpos( $componentClass, Config::$data[ "componentNamespace" ] ) === false )
		{
			return Config::$data[ "componentNamespace" ] . "\\" . $componentClass;
		}

		return $componentClass;
	}

	public static function FallbackViewNameIfNecessary( $viewName )
	{
		if( strlen( trim( $viewName ) ) == 0 )
		{
			$viewName = Config::$data[ "defaultView" ];
		}

		return $viewName;
	}

	public static function ReplaceTokenParametersInAttributes( &$model, $parameters )
	{
		$paramNodeList = $model->xPath->query( "//@*[ contains( name(), 'param' ) and contains( ., '#param' ) ]" );

		foreach( $paramNodeList as $paramNode )
		{
			$paramNode->nodeValue = $parameters[ substr( $paramNode->nodeValue, 6, -1 ) - 1 ];
		}
	}

	public static function GetHrefContextComponentAndInstanceName( $model )
	{
		// Href Context refers to the component that holds the meta:href matching the current URI
		$hrefContextComponent = "";
		$hrefContextInstanceName = "";

		$currentHref = Normalize::StripQueryInURI( Routing::URI() );
		$hrefNodeList = $model->xPath->query( "//meta:href[ text() = '" . $currentHref . "' ]" );

		if( $hrefNodeList->length > 0 )
		{
			$componentDefinitionNameNodeList = $model->xPath->query( "ancestor::wd:component[ 1 ]/@wd:name", $hrefNodeList->item( 0 ) );

			if( $componentDefinitionNameNodeList->length > 0 )
			{
				$wiredocName = $componentDefinitionNameNodeList->item( 0 )->nodeValue;
				list( $hrefContextComponent, $hrefContextInstanceName, $hrefContextFullyQualifiedName ) = ComponentUtils::ExtractComponentNamePartsFromWiredocName( $wiredocName );
			}
		}

		return array( $hrefContextComponent, $hrefContextInstanceName );
	}

	public static function CreateDefinitionAttributeIfMissing( $model, $namespace, $name, $value )
	{
		$definitionNodeList = $model->xPath->query( "//wd:component" );

		if( $definitionNodeList->length > 0 )
		{
			$definitionNode = $definitionNodeList->item( 0 );

			if( !$definitionNode->hasAttribute( $name ) )
			{
				$attribute = $model->createAttributeNS( $namespace, $name );
				$attribute->value = $value;
				$definitionNode->appendChild( $attribute );
			}
		}
	}

	public static function ExtractWiredocComponentNameFromComponentClass( $componentClass )
	{
		$nonNamespacedComponentClass = str_replace( Config::$data[ "componentNamespace" ] . "\\", "", $componentClass );
		$componentName = substr( $nonNamespacedComponentClass, 0, strrpos( $nonNamespacedComponentClass, "\\" ) );

		return str_replace( "\\", "/", $componentName );
	}

	public static function ExtractComponentNamePartsFromWiredocName( $wiredocName )
	{
		$fullyQualifiedWiredocName = self::FullyQualifyWiredocName( $wiredocName );
		$component = substr( $fullyQualifiedWiredocName, 0, strrpos( $fullyQualifiedWiredocName, "." ) );
		$instanceName = substr( strrchr( $fullyQualifiedWiredocName, "." ), 1 );

		$fullyQualifiedName = $fullyQualifiedWiredocName;

		return array( $component, $instanceName, $fullyQualifiedName );
	}

	public static function FullyQualifyWiredocName( $wiredocName )
	{
		if( strrpos( $wiredocName, "." ) === false )
		{
			$wiredocName .= ".null";
		}

		return $wiredocName;
	}
}