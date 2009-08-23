<?php

class TinyAuth
{
	var $authenticated;
	var $userDataFieldsToFetch;

	function TinyAuth()
	{
		$this->authenticated = ( isset( $_SESSION[ "authUserData" ] ) && ! is_null( $_SESSION[ "authUserData" ] ) );

		$this->userDataFieldsToFetch = array(
			"loginID",
			"created",
			"modified",
			"login"
		);
	}

	function Protect( $loginView = null )
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

				$authModel = new Model( "sql" );
				$authModel->sql->Load( "tinyauth" );
				$authModel->sql->SetQuery( "IsLoginPasswordValid" );
				$authModel->sql->SetParameters( array( ( string )$login, md5( ( string )$password ) ) );
				$authModel->sql->Execute();

				$authResults = new Model( "xml" );
				$authResults->xml->Load( $authModel );

				$query		= $authResults->xml->xPath->query( "//xmvc:loginID[1]" );
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

	function Logout()
	{
		unset( $_SESSION[ "authUserData" ] );

		$this->authenticated = false;
	}

	function SetAuthenticated( $loginID )
	{
		$userModel = new Model( "sql" );
		$userModel->sql->Load( "tinyauth" );
		$userModel->sql->SetQuery( "GetUserData" );
		$userModel->sql->SetParameters( array( ( int )$loginID ) );
		$userModel->sql->Execute();

		$userResults = new Model( "xml" );
		$userResults->xml->Load( $userModel );

		$userData = array();

		foreach( $this->userDataFieldsToFetch as $fieldName )
		{
			$query = $userResults->xml->xPath->query( "//xmvc:" . $fieldName . "[1]" );
			$userData[ $fieldName ] = $query->item( 0 )->nodeValue;
		}

		$_SESSION[ "authUserData" ] = $userData;

		$this->authenticated = true;
	}

	function IsAuthenticated()
	{
		return( $this->authenticated );
	}
}

?>