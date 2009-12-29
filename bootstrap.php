<?php

namespace xMVC\Sys
{
	class Loader
	{
		private static $defaultNamespace = "xMVC\\App";

		const controllerExtension = "php";
		const modelExtension = "xml";
		const viewExtension = "xsl";
		const driverExtension = "php";
		const libraryExtension = "php";
		const configExtension = "php";

		const controllerFolder = "controllers";
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
				$mappedFile = Normalize::Filename( $mappedFile ) . "." . $extension;
				$mappedFile = realpath( $mappedFile );

				if( $mappedFile !== false )
				{
					$mappedFolder = Normalize::Path( dirname( $mappedFile ) );
					$name = basename( $mappedFile );

					return( $mappedFolder . $name );
				}
			}

			return( false );
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

			return( $name );
		}

		public static function SetDefaultNamespace( $namespace )
		{
			self::$defaultNamespace = $namespace;
		}

		public static function GetDefaultNamespace( $namespace )
		{
			return( self::$defaultNamespace );
		}

		public static function ReadExternal( $filename )
		{
			return( file_get_contents( $filename ) );
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

			return( $result );
		}
	}

	class NamespaceMap
	{
		private static $matches;
		private static $mappings;
		private static $name;
		private static $folder;

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

				$pattern = str_replace( "::", "\\\\", $pattern );

				if( preg_match( $pattern, self::$name, self::$matches ) )
				{
					$mappedFile = preg_replace_callback( "/%([0-9]+)/", array( self, "ResolveReplaceCallback" ), $mapping );
					$mappedFile = str_replace( "%f", self::$folder, $mappedFile );

					return( array( $mappedFile ) );
				}

				return( self::Iterate() );
			}

			return( false );
		}

		private static function ResolveReplaceCallback( $matches )
		{
			return( self::$matches[ $matches[ 1 ] ] );
		}
	}

	class AutoLoad
	{
		public static function Controller( $className )
		{
			return( self::TryLoading( Loader::controllerFolder, $className, Loader::controllerExtension ) );
		}

		public static function ModelDriver( $className )
		{
			return( self::TryLoading( Loader::driverFolder, $className, Loader::driverExtension ) );
		}

		public static function Library( $className )
		{
			return( self::TryLoading( Loader::libraryFolder, $className, Loader::libraryExtension ) );
		}

		private static function TryLoading( $folder, $file, $extension )
		{
			if( ( $classFile = Loader::Resolve( $folder, $file, $extension ) ) !== false )
			{
				require_once( $classFile );

				return( true );
			}

			return( false );
		}
	}

	class Normalize
	{
		public static function MethodOrClassName( $name )
		{
			$name = str_replace( "-", "_", $name );
			$name = ucfirst( strtolower( preg_replace( "/ |\.|%20/", "", $name ) ) );

			return( $name );
		}

		public static function Filename( $name )
		{
			$name = strtolower( $name );

			return( $name );
		}

		public static function Path( $path )
		{
			$path = str_replace( "\\", "/", realpath( $path ) );
			$path = substr( $path, -1 ) != "/" ? $path . "/" : $path;

			return( $path );
		}

		public static function EncodeData( $data )
		{
			return( "/_enc_" . str_replace( "=", "_", base64_encode( serialize( $data ) ) ) );
		}

		public static function StripXMLRootTags( $xml )
		{
			// Strip xml declaration
			$xml = preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xml );

			// Strip xmvc:root
			$xml = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xml );
			$xml = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xml );

			return( $xml );
		}
	}
}

namespace
{
	use xMVC\Sys\AutoLoad;

	function __autoload( $className )
	{
		if( AutoLoad::Controller( $className ) === false )
		{
			if( AutoLoad::ModelDriver( $className ) === false )
			{
				if( AutoLoad::Library( $className ) === false )
				{
					trigger_error( "Unable to load a controller, model-driver nor a library by the name [" . $className . "]", E_USER_ERROR );
				}
			}
		}
	}
}

?>