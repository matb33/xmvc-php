<?php

srand( microtime( true ) );
set_time_limit( 0 );
session_start();

$sourceViewKey = "-src";
$sourceViewEnabled = true;

$enableInlinePHPInModels = false;
$enableInlinePHPInViews = false;

$handleErrors = true;

$useStaticControllers = false;

?>