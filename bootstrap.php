<?php

namespace xMVC\Sys
{
	class Loader
	{
		private static $matches;
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
			$path = self::FindPathWhereFileExists( $folder, $name, $extension );

			if( $path !== false )
			{
				return( $path . $name );
			}

			return( false );
		}

		public static function FindPathWhereFileExists( $folder, &$name, $extension )
		{
			$name = self::AssignDefaultNamespace( $name );

			foreach( NamespaceMap::GetMappings() as $pattern => $mapping )
			{
				$pattern = str_replace( "::", "\\\\", $pattern );

				if( preg_match( $pattern, $name, self::$matches ) )
				{
					$mappedFile = preg_replace_callback( "/%([0-9]+)/", array( self, "ResolveReplaceCallback" ), $mapping );
					$mappedFile = str_replace( "%f", $folder, $mappedFile );
					$mappedFile = Normalize::Filename( $mappedFile ) . "." . $extension;
					$mappedFile = realpath( $mappedFile );

					if( $mappedFile !== false )
					{
						$mappedFolder = Normalize::Path( dirname( $mappedFile ) );
						$name = basename( $mappedFile );

						return( $mappedFolder );
					}
				}
			}

			return( false );
		}

		private static function ResolveReplaceCallback( $matches )
		{
			return( self::$matches[ $matches[ 1 ] ] );
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
		private static $mappings;

		public static function Register( $namespacePattern, $folderMap )
		{
			self::$mappings[ $namespacePattern ] = $folderMap;
		}

		public static function GetMappings()
		{
			return( self::$mappings );
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
		public static function ObjectName( $name )
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