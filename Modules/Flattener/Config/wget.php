<?php

$wgetExeWin32 = "Modules/Flattener/bin/win32/wget.exe";
$wgetExeLinux = "Modules/Flattener/bin/linux/wget";

$isWindows = ( isset( $_SERVER[ "SystemRoot" ] ) && $_ENV[ "OS" ] == "Windows_NT" );

if( $isWindows )
{
	$wgetExecutable = $wgetExeWin32;
}
else
{
	$wgetExecutable = $wgetExeLinux;
}
