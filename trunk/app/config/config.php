<?php

srand( microtime() );
set_time_limit( 0 );
set_magic_quotes_runtime( 0 );
session_start();

$forceServerSideRendering = true;
$forceClientSideRendering = false;

$sourceViewKey = "-src";
$sourceViewEnabled = true;

$handleErrors = true;

?>