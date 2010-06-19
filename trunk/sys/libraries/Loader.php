<?php

namespace xMVC\Sys;

class Loader
{
	private static $defaultNamespace;

	const controllerExtension = "php";
	const modelExtension = "xml";
	const viewExtension = "xsl";
	const driverExtension = "php";
	const libraryExtension = "php";
	const configExtension = "php";

	const controllerFolder = "controllers";		// Stays lowercase for legacy support reasons
	const modelFolder = "models";
	const viewFolder = "views";
	const driverFolder = "drivers";
	const libraryFolder = "libraries";
	const configFolder = "config";

	public static function Resolve( $folder, $name, $extension )
	{
		$name = self::AssignDefaultNamespace( $name );

		NamespaceMap::SetName( $name );
		NamespaceMap::SetFolder( $folder );
		NamespaceMap::RewindIterator();

		foreach( NamespaceMap::Iterate() as $mappedFile )
		{
			$file = Normalize::Filename( $mappedFile ) . "." . $extension;
			$file = realpath( $file );

			if( $file === false )
			{
				$file = Normalize::Filename( $mappedFile );
				$file = realpath( $file );
			}

			if( $file !== false )
			{
				$dirName = Normalize::Path( dirname( $file ) );
				$name = basename( $file );

				return $dirName . $name;
			}
		}

		return false;
	}

	public static function AssignDefaultNamespace( $name, $forcedNamespace = null )
	{
		if( strpos( $name, "\\" ) === false )
		{
			$name = self::$defaultNamespace . "\\" . $name;
		}

		if( ! is_null( $forcedNamespace ) )
		{
			$name = $forcedNamespace . substr( $name, strrpos( $name, "\\" ) );
		}

		return $name;
	}

	public static function ExtractNamespace( $name )
	{
		$namespace = substr( $name, 0, strrpos( $name, "\\" ) );

		if( strlen( $namespace ) > 0 )
		{
			return $namespace;
		}
		else
		{
			return null;
		}
	}

	public static function StripNamespace( $name )
	{
		return str_replace( "\\", "", substr( $name, strrpos( $name, "\\" ) ) );
	}

	public static function SetDefaultNamespace( $namespace )
	{
		self::$defaultNamespace = $namespace;
	}

	public static function GetDefaultNamespace( $namespace )
	{
		return self::$defaultNamespace;
	}

	public static function ReadExternal( $filename )
	{
		return file_get_contents( $filename );
	}

	public static function ParseExternal( $filename, $data )
	{
		if( ! isset( $data[ "encodedData" ] ) )
		{
			$data[ "encodedData" ] = Normalize::EncodeData( $data );
		}

		if( ! is_null( $data ) && is_array( $data ) )
		{
			extract( $data, EXTR_SKIP );
		}

		ob_start();
		include( $filename );
		$result = ob_get_contents();
		ob_end_clean();

		return $result;
	}

	public static function AddToIncludePath( $path )
	{
		$currentPath = explode( PATH_SEPARATOR, get_include_path() );
		array_push( $currentPath, $path );
		$uniquedPath = array_unique( $currentPath );
		set_include_path( implode( PATH_SEPARATOR, $uniquedPath ) );
	}
}