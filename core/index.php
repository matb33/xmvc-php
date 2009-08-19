<?php

//==============================================================================
// PHP configuration
//==============================================================================

srand( (double)microtime() * 1268938 );

set_time_limit( 0 );
set_magic_quotes_runtime( 0 );

// set maximum time in seconds a script is allowed to parse input data, like POST, GET and file uploads
ini_set( "max_input_time", 3600 );

if( strpos( $_SERVER[ "HTTP_USER_AGENT" ], "google" ) !== false )
{
	ini_set( "session.use_cookies",			"1" );		// if google is crawling the site, disabled PHPSESSID in URL's
	ini_set( "session.use_only_cookies",	"1" );
	ini_set( "session.use_trans_sid",		"0" );
}
else
{
	ini_set( "session.use_cookies",			"1" );		// preferably don't show PHPSESSID in URL's (use cookies to pass PHPSESSID)
	ini_set( "session.use_only_cookies",	"1" );		// but if they have cookies disabled, then PHPSESSID by URL is OK
	ini_set( "session.use_trans_sid",		"0" );
}

session_start();

//==============================================================================
// Specify paths to application and system folders
//==============================================================================

// Define paths relative to index.php

$appPath = "app/";
$sysPath = "sys/";

$appPath = str_replace( "\\", "/", realpath( $appPath ) );
$sysPath = str_replace( "\\", "/", realpath( $sysPath ) );

define( "APP_PATH",	( substr( $appPath, -1 ) != "/" ? $appPath . "/" : $appPath ) );
define( "SYS_PATH",	( substr( $sysPath, -1 ) != "/" ? $sysPath . "/" : $sysPath ) );

//==============================================================================
// Initialize xMVC
//==============================================================================

require_once( SYS_PATH . "root.php" );
require_once( SYS_PATH . "config.php" );
require_once( SYS_PATH . "xmvc.php" );
require_once( SYS_PATH . "controller.php" );
require_once( SYS_PATH . "model.php" );
require_once( SYS_PATH . "view.php" );
require_once( SYS_PATH . "driver.php" );
require_once( SYS_PATH . "error.php" );

xMVC::URI();
xMVC::URIProtocol();

Config::Load( SYS_PATH );
Config::Load( APP_PATH );

xMVC::DefaultController( Config::Value( "defaultController" ) );
xMVC::Routes( Config::Value( "routes" ) );

xMVC::PathData();
xMVC::Controller();

xMVC::HandleErrors( Config::Value( "handleErrors" ) );

if( xMVC::HandleErrors() )
{
	xMVC::ErrorHandler( new ErrorHandler() );
}

xMVC::SourceViewKey( Config::Value( "sourceViewKey" ) );
xMVC::SourceViewEnabled( Config::Value( "sourceViewEnabled" ) );

//==============================================================================
// Start application
//==============================================================================

xMVC::RootController( xMVC::InstantiateRootController() );

?>