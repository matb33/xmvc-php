<?php

namespace xMVC\Mod\WireKit\Components;

use xMVC\Sys\Loader;
use xMVC\Sys\Config;

class ComponentUtils
{
	public static function RegisterNamespaces( &$model )
	{
		foreach( Config::$data[ "wirekitNamespaces" ] as $prefix => $namespace )
		{
			$model->xPath->registerNamespace( $prefix, $namespace );
		}
	}

	public static function GetComponentClassName( $component )
	{
		$fullyQualifiedName = self::GetFullyQualifiedComponent( $component );
		$componentClass = $fullyQualifiedName . strrchr( $fullyQualifiedName, "\\" );

		return( $componentClass );
	}

	public static function GetComponentDefinitionFilename( $component )
	{
		return( self::GetComponentClassName( $component ) . ".xsl" );
	}

	public static function GetFullyQualifiedComponent( $component )
	{
		if( Loader::Resolve( null, $component, Loader::modelExtension ) !== false )
		{
			// Is an FQN with instance-name specified
			return( $component );
		}
		elseif( Loader::Resolve( "libraries", $component, Loader::libraryExtension ) !== false )
		{
			// Is likely the GenericComponent class found in the libraries/Components structure
			return( $component );
		}
		elseif( ComponentLookup::getInstance()->GetComponentDataByComponentName( $component ) !== false )
		{
			return( $component );
		}

		return( Config::$data[ "componentNamespace" ] . "\\" . $component );
	}

	public static function DefaultEventNameIfNecessary( $eventName )
	{
		if( is_null( $eventName ) || strlen( $eventName ) == 0 )
		{
			return( "default.components" );
		}

		return( $eventName );
	}

	public static function FallbackViewNameIfNecessary( $viewName )
	{
		if( strlen( trim( $viewName ) ) == 0 )
		{
			$viewName = "xMVC\\Mod\\WireKit\\xhtml1-strict";
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
		// Href Context refers to the component that holds the meta:href (specifically the last occurence (should it be the deepest??))

		$hrefContextComponent = "";
		$hrefContextInstanceName = "";

		$hrefNodeList = $model->xPath->query( "//meta:href" );

		if( $hrefNodeList->length > 0 )
		{
			$componentDefinitionNodeList = $model->xPath->query( "ancestor::component:definition[1]", $hrefNodeList->item( $hrefNodeList->length - 1 ) );

			if( $componentDefinitionNodeList->length > 0 )
			{
				$componentDefinitionNode = $componentDefinitionNodeList->item( 0 );
				$hrefContextComponent = $componentDefinitionNode->hasAttribute( "name" ) ? $componentDefinitionNode->getAttribute( "name" ) : "";
				$hrefContextInstanceName = $componentDefinitionNode->hasAttribute( "instance-name" ) ? $componentDefinitionNode->getAttribute( "instance-name" ) : "";
			}
		}

		return( array( $hrefContextComponent, $hrefContextInstanceName ) );
	}

	public static function CreateDefinitionAttributeIfMissing( $model, $name, $value )
	{
		$definitionNodeList = $model->xPath->query( "//component:definition" );

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

	// TODO: This function should probably check against ComponentLookup instead of assuming stuff
	public static function ExtractComponentNameParts( $componentString, $instanceName = null )
	{
		$fullyQualifiedName = self::GetFullyQualifiedComponent( $componentString );

		if( is_null( $instanceName ) || strlen( $instanceName ) == 0 )
		{
			$component = substr( $fullyQualifiedName, 0, strrpos( $fullyQualifiedName, "\\" ) );
			$instanceName = substr( strrchr( $fullyQualifiedName, "\\" ), 1 );
		}
		else
		{
			$component = $fullyQualifiedName;
			$fullyQualifiedName .= "\\" . $instanceName;
		}

		return( array( $component, $instanceName, $fullyQualifiedName ) );
	}

	public static function ExtractComponentFromComponentClass( $componentClass )
	{
		return( substr( $componentClass, 0, strrpos( $componentClass, "\\" ) ) );
	}
}