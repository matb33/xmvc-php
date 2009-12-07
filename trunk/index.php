<?php

$appPathRelativeToIndex = "app/";
$sysPathRelativeToIndex = "sys/";

$appPath = str_replace( "\\", "/", realpath( $appPathRelativeToIndex ) );
$sysPath = str_replace( "\\", "/", realpath( $sysPathRelativeToIndex ) );

define( "APP_PATH",	( substr( $appPath, -1 ) != "/" ? $appPath . "/" : $appPath ) );
define( "SYS_PATH",	( substr( $sysPath, -1 ) != "/" ? $sysPath . "/" : $sysPath ) );

require_once( SYS_PATH . "xmvc.php" );

xMVC::Load();

?>