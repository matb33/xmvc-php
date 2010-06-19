<?php

namespace Module\GData;

use System\Drivers\XMLModelDriver;

class YouTube
{
	private static function InitializeZend()
	{
		@session_start();
		@set_include_path( get_include_path() . PATH_SEPARATOR . "mod/youtube/libraries/" );

		require_once( "Zend/Uri/Http.php" );
		require_once( "Zend/Http/Client/Adapter/Socket.php" );
		require_once( "Zend/Gdata/ClientLogin.php" );
		require_once( "Zend/Gdata/YouTube.php" );
		require_once( "Zend/Gdata/YouTube/VideoEntry.php" );
	}

	public static function TokenRequest( $username, $password, $title, $description )
	{
		self::InitializeZend();

		try
		{
			$httpClient = \Zend_Gdata_ClientLogin::getHttpClient(
				$username,
				$password,
				"youtube",
				null,
				"YouTube Uploader",
				null,
				null,
				"https://www.google.com/youtube/accounts/ClientLogin"
			);

			$developerKey = Config::$data[ "youTubeDeveloperKey" ];
			$applicationId = "N/A";
			$clientId = "";

			$yt = new \Zend_Gdata_YouTube( $httpClient, $applicationId, $clientId, $developerKey );
			$yt->setMajorProtocolVersion( 2 );

			// create a new VideoEntry object
			$videoEntry = new \Zend_Gdata_YouTube_VideoEntry();

			$videoEntry->setVideoTitle( $title );
			$videoEntry->setVideoDescription( $description );

			// The category must be a valid YouTube category!
			$videoEntry->setVideoCategory( Config::$data[ "youTubeVideoCategory" ] );

			// Set keywords. Please note that this must be a comma-separated string
			// and that individual keywords cannot contain whitespace
			$videoEntry->SetVideoTags( str_replace( " ", "_", implode( ",", Config::$data[ "youTubeVideoTagArray" ] ) ) );

			$tokenArray = $yt->getFormUploadToken( $videoEntry, "http://gdata.youtube.com/action/GetUploadToken" );

			$return[ "token" ] = $tokenArray[ "token" ];
			$return[ "postURL" ] = $tokenArray[ "url" ];
			$return[ "success" ] = true;
		}
		catch( \Exception $err )
		{
			$return[ "success" ] = false;
		}
	}
}

?>