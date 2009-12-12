<?php

srand( microtime() );
set_time_limit( 0 );
session_start();
date_default_timezone_set( "America/New_York" );

$sourceViewKey = "-src";
$sourceViewEnabled = true;

$enableInlinePHPInModels = false;
$enableInlinePHPInViews = false;

$handleErrors = true;

?>