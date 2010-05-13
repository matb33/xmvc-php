<?php

// TODO: Consider renaming all methods across the board to camelCase so that it matches what appears to be the standard across PHP and JavaScript, which is what xMVC interacts with the most
// TODO: Debate creating a standard "data" folder for what xMVC currently calls "models" (XML files), and renaming ModelDriver to Model.  Does this make more sense, or is the current method fine?

namespace xMVC\Sys
{
	class Loader
	{
		private static $defaultNamespace;

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

					return( $dirName . $name );
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

		public static function ExtractNamespace( $name )
		{
			$namespace = substr( $name, 0, strrpos( $name, "\\" ) );

			if( strlen( $namespace ) > 0 )
			{
				return( $namespace );
			}
			else
			{
				return( null );
			}
		}

		public static function StripNamespace( $name )
		{
			return( str_replace( "\\", "", substr( $name, strrpos( $name, "\\" ) ) ) );
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

		public static function AddToIncludePath( $path )
		{
			$currentPath = explode( PATH_SEPARATOR, get_include_path() );
			array_push( $currentPath, $path );
			$uniquedPath = array_unique( $currentPath );
			set_include_path( implode( PATH_SEPARATOR, $uniquedPath ) );
		}
	}

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

					return( array( $mappedFile ) );
				}

				return( self::Iterate() );
			}

			return( array() );
		}

		private static function ResolveReplaceCallback( $matches )
		{
			return( self::$matches[ $matches[ 1 ] ] );
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
					return( $path );
				}
			}

			return( false );
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
			$name = preg_replace( "/-|_/", " ", $name );
			$name = str_replace( "\\", "          ", $name );
			$name = ucwords( $name );
			$name = str_replace( "          ", "\\", $name );
			$name = preg_replace( "/ |\.|%20/", "", $name );
			$name = str_replace( "XMVC", "xMVC", $name );

			return( $name );
		}

		public static function Filename( $name )
		{
			$name = str_replace( "\\", "/", $name );

			return( $name );
		}

		public static function Path( $path )
		{
			$path = str_replace( "\\", "/", realpath( str_replace( "\\", "/", $path ) ) );
			$path = substr( $path, -1 ) != "/" ? $path . "/" : $path;

			return( $path );
		}

		public static function EncodeData( $data )
		{
			return( "/_enc_" . str_replace( "=", "_", base64_encode( serialize( $data ) ) ) );
		}

		public static function StripXMLRootTags( $xml )
		{
			$xml = self::StripXMLDeclaration( $xml );
			$xml = self::StripRootTag( $xml );

			return( $xml );
		}

		public static function StripXMLDeclaration( $xml )
		{
			return( preg_replace( "|<\?xml(.+?)\?>[\n\r]?|i", "", $xml ) );
		}

		public static function StripRootTag( $xml )
		{
			$xml = preg_replace( "|<xmvc:root(.+?)>[\n\r]?|", "", $xml );
			$xml = preg_replace( "|<\/xmvc:root>[\n\r]?|", "", $xml );

			return( $xml );
		}

		public static function StripQueryInURI( $uri )
		{
			return( preg_replace( "/\?.*$/", "", $uri ) );
		}

		public static function URI( $uri )
		{
			$uri = str_replace( "/index.php", "/", $uri );
			$uri = preg_replace( "/[\/]{2,}/", "/", $uri );

			return( $uri );
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
					//trigger_error( "Unable to load a controller, model-driver nor a library by the name [" . $className . "]", E_USER_ERROR );
				}
			}
		}
	}
}

?>