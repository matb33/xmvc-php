<?php

namespace Module\SVN;

use System\Libraries\Config;
use System\Libraries\Debug;

class SVNErrors
{
	private static $output;
	private static $subCommand;

	public static function Analyze( $output, $subCommand )
	{
		self::$output = $output;
		self::$subCommand = $subCommand;

		$matchMap = array();
		$matchMap[ "/401 Authorization Required/" ] = "CredentialError";
		$matchMap[ "/could not connect to server/" ] = "ServerConnectError";
		$matchMap[ "/path not found/" ] = "RepositoryPathError";
		$matchMap[ "/403 Forbidden/" ] = "ForbiddenError";
		$matchMap[ "/Server certificate verification failed/" ] = "ServerCertificateError";

		foreach( $matchMap as $pattern => $method )
		{
			if( preg_match( $pattern, $output ) )
			{
				call_user_func( "self::" . $method );
			}
		}

		Debug::WriteAndDump( "SVNOutput Analyze", $output );
	}

	private static function CredentialError()
	{
		trigger_error( "SVN error: Could not connect to SVN repository: Username/password incorrect.", E_USER_ERROR );
	}

	private static function ServerConnectError()
	{
		trigger_error( "SVN error: Could not connect to SVN repository server.", E_USER_ERROR );
	}

	private static function RepositoryPathError()
	{
		trigger_error( "SVN error: Repository path could not be found.", E_USER_ERROR );
	}

	private static function ForbiddenError()
	{
		trigger_error( "SVN error: Forbidden. This could be due to a malformed repository URL.", E_USER_ERROR );
	}

	private static function ServerCertificateError()
	{
		trigger_error( "SVN error: The server certificate verification failed.  You will need to run svn.exe manually and accept the certificate permanently.", E_USER_ERROR );
	}
}

?>