<?php

// TO-DO: Test this under the new PHP 5 infrastructure

class TinyAuth
{
	private static $userDataFieldsToFetch = array( "loginID", "created", "modified", "login" );
	private static $authenticated = null;

	public static function Protect( $loginView = null )
	{
		$authenticated		= false;
		$incorrectLogin		= false;
		$login				= "";

		if( ! $this->IsAuthenticated() )
		{
			if( isset( $_POST[ "loginbutton" ] ) )
			{
				$login		= $_POST[ "login" ];
				$password	= $_POST[ "password" ];

				$authModel = new SQLModelDriver();
				$authModel->Load( "tinyauth" );
				$authModel->SetQuery( "IsLoginPasswordValid" );
				$authModel->SetParameters( array( ( string )$login, md5( ( string )$password ) ) );
				$authModel->Execute();

				$authResults = new XMLModelDriver();
				$authResults->Load( $authModel );

				$query		= $authResults->xPath->query( "//xmvc:loginID[1]" );
				$loginID	= $query->item( 0 )->nodeValue;

				$authenticated = ( ! is_null( $loginID ) );

				if( $authenticated )
				{
					$this->SetAuthenticated( $loginID );
				}
				else
				{
					$incorrectLogin = true;
				}
			}

			if( ! $this->IsAuthenticated() )
			{
				if( is_null( $loginView ) )
				{
					$loginView = new View();
					$loginView->Render( "tinyauth", array( "login" => $login, "incorrectLogin" => $incorrectLogin ) );
				}
				else
				{
					$loginView->Render();
				}
			}
		}
		else
		{
			$authenticated = true;
		}

		return( $authenticated );
	}

	public static function Logout()
	{
		unset( $_SESSION[ "authUserData" ] );

		self::$authenticated = false;
	}

	private static function SetAuthenticated( $loginID )
	{
		$userModel = new SQLModelDriver();
		$userModel->Load( "tinyauth" );
		$userModel->SetQuery( "GetUserData" );
		$userModel->SetParameters( array( ( int )$loginID ) );
		$userModel->Execute();

		$userResults = XMLModelDriver();
		$userResults->Load( $userModel );

		$userData = array();

		foreach( $this->userDataFieldsToFetch as $fieldName )
		{
			$userData[ $fieldName ] = $userResults->xPath->query( "//xmvc:" . $fieldName . "[1]" )->item( 0 )->nodeValue;
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