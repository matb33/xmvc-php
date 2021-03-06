<?php

namespace xMVC\Mod\Utils;

use xMVC\Sys\Loader;
use xMVC\Mod\Language\Language;

class StringUtils
{
	public static function ReplaceTokensInPattern( $pattern, $tokenValuePairs, $tokenDelimiter = "#" )
	{
		$builtInTokenValuePairs = array();
		$builtInTokenValuePairs[ "controllerFolder" ] = Loader::controllerFolder;
		$builtInTokenValuePairs[ "modelFolder" ] = Loader::modelFolder;
		$builtInTokenValuePairs[ "viewFolder" ] = Loader::viewFolder;
		$builtInTokenValuePairs[ "driverFolder" ] = Loader::driverFolder;
		$builtInTokenValuePairs[ "libraryFolder" ] = Loader::libraryFolder;
		$builtInTokenValuePairs[ "configFolder" ] = Loader::configFolder;

		if( !isset( $tokenValuePairs[ "lang" ] ) )
		{
			$builtInTokenValuePairs[ "lang" ] = Language::GetLang();
		}

		foreach( array_merge( $builtInTokenValuePairs, $tokenValuePairs ) as $token => $value )
		{
			$pattern = str_replace( $tokenDelimiter . $token . $tokenDelimiter, $value, $pattern );
		}

		return $pattern;
	}
}