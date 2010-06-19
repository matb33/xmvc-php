<?php

// Note that this method, despite being very flexible due to the inherent use of regular expression namespace to folder mapping technique, is
// far slower than the spl_autoload namespace direct to folder representation.  Because of this, newer installations of xMVC should not use
// the traditional xMVC\App, xMVC\Mod, xMVC\Sys, etc. namespaces, but instead namespaces that map directly to folders.  For legacy
// compatibility reasons, the traditional regular expression technique remains functional, but is *second* in the autoload queue.

namespace System\Libraries;

class AutoLoad
{
	public static function Controller( $className )
	{
		return self::TryLoading( Loader::controllerFolder, $className, Loader::controllerExtension );
	}

	public static function ModelDriver( $className )
	{
		return self::TryLoading( Loader::driverFolder, $className, Loader::driverExtension );
	}

	public static function Library( $className )
	{
		return self::TryLoading( Loader::libraryFolder, $className, Loader::libraryExtension );
	}

	private static function TryLoading( $folder, $file, $extension )
	{
		if( ( $classFile = Loader::Resolve( $folder, $file, $extension ) ) !== false )
		{
			require_once( $classFile );
			return true;
		}

		return false;
	}

	public static function Callback( $className )
	{
		if( self::Controller( $className ) === false )
		{
			if( self::ModelDriver( $className ) === false )
			{
				if( self::Library( $className ) === false )
				{
					return false;
				}
			}
		}

		return true;
	}
}