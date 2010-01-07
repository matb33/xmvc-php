<?php

namespace Module\TinyAuth;

use xMVC\Sys\Loader;
use xMVC\Sys\StringsModelDriver;
use xMVC\Sys\SQLModelDriver;
use xMVC\Sys\View;

@session_start();

class Authenticator
{
	private static $userDataFieldsToFetch = array( "loginID", "created", "modified", "login" );
	private static $authenticated = null;

	public static function Protect( $loginView = null )
	{
		$authenticated = false;
		$incorrectLogin = false;
		$login = "";

		if( ! self::IsAuthenticated() )
		{
			if( isset( $_POST[ "loginbutton" ] ) )
			{
				$login = $_POST[ "login" ];
				$password = $_POST[ "password" ];

				$authModel = new SQLModelDriver( __NAMESPACE__ . "\\queries" );
				$authModel->UseQuery( "IsLoginPasswordValid" );
				$authModel->SetParameters( array( ( string )$login, md5( ( string )$password ) ) );
				$authModel->Execute();

				$loginID = $authModel->xPath->query( "//xmvc:column[@name='loginID']" )->item( 0 )->nodeValue;

				$authenticated = ( ! is_null( $loginID ) );

				if( $authenticated )
				{
					self::SetAuthenticated( $loginID );
				}
				else
				{
					$incorrectLogin = true;
				}
			}

			if( ! self::IsAuthenticated() )
			{
				if( is_null( $loginView ) )
				{
					$strings = new StringsModelDriver();
					$strings->Add( "login", $login );
					$strings->Add( "incorrect-login", $incorrectLogin ? "true" : "false" );

					$loginView = new View( __NAMESPACE__ . "\\login" );
					$loginView->PushModel( $strings );
					$loginView->RenderAsHTML();
				}
				else
				{
					$loginView->RenderAsHTML();
				}
			}
		}
		else
		{
			$authenticated = true;
		}

		return( $authenticated );
	}

	public static function GetUserData( $key )
	{
		return( $_SESSION[ "authUserData" ][ $key ] );
	}

	public static function Logout()
	{
		unset( $_SESSION[ "authUserData" ] );

		self::$authenticated = false;
	}

	private static function SetAuthenticated( $loginID )
	{
		$userModel = new SQLModelDriver( __NAMESPACE__ . "\\queries" );
		$userModel->UseQuery( "GetUserData" );
		$userModel->SetParameters( array( ( int )$loginID ) );
		$userModel->Execute();

		$userData = array();

		foreach( self::$userDataFieldsToFetch as $fieldName )
		{
			$userData[ $fieldName ] = $userModel->xPath->query( "//xmvc:column[@name='" . $fieldName . "']" )->item( 0 )->nodeValue;
		}

		$_SESSION[ "authUserData" ] = $userData;

		self::$authenticated = true;
	}

	public static function IsAuthenticated()
	{
		if( is_null( self::$authenticated ) )
		{
			self::$authenticated = ( isset( $_SESSION[ "authUserData" ] ) && ! is_null( $_SESSION[ "authUserData" ] ) );
		}

		return( self::$authenticated );
	}
}

?>