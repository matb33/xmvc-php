<?php

namespace System\Libraries;

class NamespaceMap
{
	private static $matches = array();
	private static $mappings = array();
	private static $name = "";
	private static $folder = "";

	public static function Register( $namespacePattern, $folderMap )
	{
		self::$mappings[ $namespacePattern ] = $folderMap;
	}

	public static function SetName( $name )
	{
		self::$name = $name;
	}

	public static function SetFolder( $folder )
	{
		self::$folder = $folder;
	}

	public static function RewindIterator()
	{
		reset( self::$mappings );
	}

	public static function Iterate()
	{
		$pair = each( self::$mappings );

		if( $pair !== false )
		{
			list( $pattern, $mapping ) = $pair;

			$pattern = str_replace( "\\", "\\\\", $pattern );

			if( preg_match( $pattern, self::$name, self::$matches ) )
			{
				$mappedFile = preg_replace_callback( "/%([0-9]+)/", "self::ResolveReplaceCallback", $mapping );
				$mappedFile = str_replace( "%f", self::$folder, $mappedFile );

				return array( $mappedFile );
			}

			return self::Iterate();
		}

		return array();
	}

	private static function ResolveReplaceCallback( $matches )
	{
		return self::$matches[ $matches[ 1 ] ];
	}

	public static function NamespaceToFolder( $namespace )
	{
		NamespaceMap::SetName( $namespace );
		NamespaceMap::SetFolder( "" );
		NamespaceMap::RewindIterator();

		foreach( NamespaceMap::Iterate() as $mappedFile )
		{
			$path = Normalize::Filename( $mappedFile );
			$path = realpath( $path );

			if( $path !== false )
			{
				return $path;
			}
		}

		return false;
	}
}