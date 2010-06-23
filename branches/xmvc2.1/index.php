<?php

namespace System\Libraries;

spl_autoload_register( function( $className ) { @require_once str_replace( "\\", "/", $className ) . ".php"; } );

set_error_handler( "System\\Libraries\\ErrorHandler::ExceptionErrorHandler" );
set_exception_handler( "System\\Libraries\\ErrorHandler::UncaughtExceptionHandler" );

Loader::setDefaultNamespace( "Application" );
Config::load( "./System", "./Modules/WebCommon", "./Modules/*", "./Application" );
FrontController::load();