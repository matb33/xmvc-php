<?php

namespace Modules\Utils\Libraries;

use System\Libraries\Loader;
use Modules\Language\Libraries\Language;

class StringUtils
{
	public static function replaceTokensInPattern( $pattern, $tokenValuePairs, $tokenDelimiter = "#" )
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
			$builtInTokenValuePairs[ "lang" ] = Language::getLang();
		}

		$pairs = array_merge( $builtInTokenValuePairs, $tokenValuePairs );

		foreach( $pairs as $token => $value )
		{
			$pattern = str_replace( $tokenDelimiter . $token . $tokenDelimiter, $value, $pattern );
		}

		return $pattern;
	}
}