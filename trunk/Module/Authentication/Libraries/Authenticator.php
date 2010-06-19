<?php

namespace Module\Authentication;

use System\Drivers\SQLModelDriver;

class Authenticator
{
	private static $authenticated = null;

	public static function Authenticate( $username, $password )
	{
		$userID = null;
		$authenticated = false;

		if( ! self::IsAuthenticated() )
		{
			$authModel = new SQLModelDriver( __NAMESPACE__ . "\\authentication" );
			$authModel->UseQuery( "IsUserPasswordValid" );
			$authModel->SetParameters( array( ( string )$username, md5( ( string )$password ) ) );
			$authModel->Execute();

			$userNodeList = $authModel->xPath->query( "//xmvc:column[ @name='userid' ]" );

			if( $userNodeList->length > 0 )
			{
				$userID = $userNodeList->item( 0 )->nodeValue;
			}

			$authenticated = ( ! is_null( $userID ) );

			if( $authenticated )
			{
				self::SetAuthenticated( $userID );
			}
		}
		else
		{
			$authenticated = true;
		}

		return $authenticated;
	}

	public static function GetUserData( $key )
	{
		return $_SESSION[ "authUserData" ][ $key ];
	}

	public static function Logout()
	{
		unset( $_SESSION[ "authUserData" ] );

		self::$authenticated = false;
	}

	private static function SetAuthenticated( $userID )
	{
		$userModel = new SQLModelDriver( __NAMESPACE__ . "\\authentication" );
		$userModel->UseQuery( "GetUserData" );
		$userModel->SetParameters( array( ( int )$userID ) );
		$userModel->Execute();

		$userData = array();

		foreach( $userModel->xPath->query( "//xmvc:column" ) as $node )
		{
			$fieldName = $node->getAttribute( "name" );
			$fieldValue = $node->nodeValue;

			$userData[ $fieldName ] = $fieldValue;
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

		return self::$authenticated;
	}

	public function GetStateFromModel( $model )
	{
		$stateNodeList = $model->xPath->query( "//wd:component/@state" );
		$state = $stateNodeList->length > 0 ? $stateNodeList->item( 0 )->nodeValue : "neutral";

		return $state;
	}
}