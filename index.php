<?php

namespace System;

include( "namespaces.php" );

Libraries\Routing::singletonOverride( "Application\\Libraries\\MyRouting" );
Libraries\FrontController::singletonOverride( "Application\\Libraries\\MyFrontController" );

Libraries\Routing::getInstance()->helloWorld();
Libraries\FrontController::getInstance()->helloWorld();