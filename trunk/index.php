<?php

namespace xMVC;

$appPathRelativeToIndex = "app/";
$sysPathRelativeToIndex = "sys/";
$modPathRelativeToIndex = "mod/";

$appPath = str_replace( "\\", "/", realpath( $appPathRelativeToIndex ) );
$sysPath = str_replace( "\\", "/", realpath( $sysPathRelativeToIndex ) );
$modPath = str_replace( "\\", "/", realpath( $modPathRelativeToIndex ) );

define( "APP_PATH", ( substr( $appPath, -1 ) != "/" ? $appPath . "/" : $appPath ) );
define( "SYS_PATH", ( substr( $sysPath, -1 ) != "/" ? $sysPath . "/" : $sysPath ) );
define( "MOD_PATH", ( substr( $modPath, -1 ) != "/" ? $modPath . "/" : $modPath ) );

require_once( SYS_PATH . "core.php" );

Core::Load();

?>