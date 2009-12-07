<?php

srand( microtime() );
set_time_limit( 0 );
set_magic_quotes_runtime( 0 );
session_start();

$defaultController = "home";
$useQueryInRoutes = false;

$routes[ "/favicon\.ico(.*)/" ] = "favicon/ico$1";
$routes[ "/favicon\.gif(.*)/" ] = "favicon/gif$1";
$routes[ "/favicon\.png(.*)/" ] = "favicon/png$1";

$forceServerSideRendering = true;
$forceClientSideRendering = false;

$databaseHost = "localhost";
$databaseName = "";
$databaseUser = "";
$databasePass = "";
$databaseType = "mysqli";

$sourceViewKey = "-src";
$sourceViewEnabled = true;

$handleErrors = true;

?>