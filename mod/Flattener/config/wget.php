<?php

$wgetExeWin32 = "mod/Flattener/bin/win32/wget-1.12.exe";
$wgetExeLinux = "mod/Flattener/bin/linux/wget";

$isWindows = ( isset( $_SERVER[ "SystemRoot" ] ) && $_ENV[ "OS" ] == "Windows_NT" );

if( $isWindows )
{
	$wgetExecutable = $wgetExeWin32;
}
else
{
	$wgetExecutable = $wgetExeLinux;
}
