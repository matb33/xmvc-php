<?php

class TinyAuth
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

				$authModel = new SQLModelDriver();
				$authModel->Load( "queries/tinyauth" );
				$authModel->SetQuery( "IsLoginPasswordValid" );
				$authModel->SetParameters( array( ( string )$login, md5( ( string )$password ) ) );
				$authModel->Execute();

				$loginID = $authModel->xPath->query( "//xmvc:column[@xmvc:name='loginID']" )->item( 0 )->nodeValue;

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

					$loginView = new View( "tinyauth" );
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
		$userModel = new SQLModelDriver();
		$userModel->Load( "queries/tinyauth" );
		$userModel->SetQuery( "GetUserData" );
		$userModel->SetParameters( array( ( int )$loginID ) );
		$userModel->Execute();

		$userData = array();

		foreach( self::$userDataFieldsToFetch as $fieldName )
		{
			$userData[ $fieldName ] = $userModel->xPath->query( "//xmvc:column[@xmvc:name='" . $fieldName . "']" )->item( 0 )->nodeValue;
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