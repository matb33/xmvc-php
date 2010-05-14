<?php

namespace
{
	$moduleRegistry = array( "Application", "System" );

	include( "autoload.php" );

	Libraries\Routing::override( "Libraries\\MyRouting" );
	Libraries\FrontController::override( "Libraries\\MyFrontController" );

	Libraries\Routing::getInstance()->helloWorld();
	Libraries\FrontController::getInstance()->helloWorld();
}