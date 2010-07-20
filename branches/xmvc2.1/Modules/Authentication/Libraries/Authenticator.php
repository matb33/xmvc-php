<?php

namespace Modules\Authentication\Libraries;

use System\Drivers\SQLModelDriver;

class Authenticator
{
	private static $authenticated = null;

	public static function authenticate( $username, $password )
	{
		$userID = null;
		$authenticated = false;

		if( ! self::isAuthenticated() )
		{
			$authModel = new SQLModelDriver( "Modules\\Authentication\\Models\\authentication" );
			$authModel->useQuery( "IsUserPasswordValid" );
			$authModel->setParameters( array( ( string )$username, md5( ( string )$password ) ) );
			$authModel->execute();

			$userNodeList = $authModel->xPath->query( "//xmvc:column[ @name='userid' ]" );

			if( $userNodeList->length > 0 )
			{
				$userID = $userNodeList->item( 0 )->nodeValue;
			}

			$authenticated = ( ! is_null( $userID ) );

			if( $authenticated )
			{
				self::setAuthenticated( $userID );
			}
		}
		else
		{
			$authenticated = true;
		}

		return $authenticated;
	}

	public static function getUserData( $key )
	{
		return $_SESSION[ "authUserData" ][ $key ];
	}

	public static function logout()
	{
		unset( $_SESSION[ "authUserData" ] );

		self::$authenticated = false;
	}

	private static function setAuthenticated( $userID )
	{
		$userModel = new SQLModelDriver( "Modules\\Authentication\\Models\\authentication" );
		$userModel->useQuery( "GetUserData" );
		$userModel->setParameters( array( ( int )$userID ) );
		$userModel->execute();

		$userData = array();

		$nodeList = $userModel->xPath->query( "//xmvc:column" );

		foreach( $nodeList as $node )
		{
			$fieldName = $node->getAttribute( "name" );
			$fieldValue = $node->nodeValue;

			$userData[ $fieldName ] = $fieldValue;
		}

		$_SESSION[ "authUserData" ] = $userData;

		self::$authenticated = true;
	}

	public static function isAuthenticated()
	{
		if( is_null( self::$authenticated ) )
		{
			self::$authenticated = ( isset( $_SESSION[ "authUserData" ] ) && ! is_null( $_SESSION[ "authUserData" ] ) );
		}

		return self::$authenticated;
	}

	public static function getStateFromModel( $model )
	{
		$stateNodeList = $model->xPath->query( "//wd:component/@state" );
		$state = $stateNodeList->length > 0 ? $stateNodeList->item( 0 )->nodeValue : "neutral";

		return $state;
	}
}