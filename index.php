<?php

namespace System;

include( "namespaces.php" );

Libraries\Routing::override( "Application\\Libraries\\MyRouting" );
Libraries\FrontController::override( "Application\\Libraries\\MyFrontController" );

Libraries\Routing::getInstance()->helloWorld();
Libraries\FrontController::getInstance()->helloWorld();