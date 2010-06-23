<?php

$svnExeWin32 = "Modules/SVN/bin/win32/svn.exe";
$svnExeLinux = "Modules/SVN/bin/linux/svn";
$svnConfigFolder = "Modules/SVN/bin/config/";
$svnWorkingFolder = "Modules/SVN/work/";	// Not used by this module's code, here only as an option for you to use as a working folder that already has svn:ignore setup on it

$isWindows = ( isset( $_SERVER[ "SystemRoot" ] ) && $_ENV[ "OS" ] == "Windows_NT" );

if( $isWindows )
{
	$svnExecutable = $svnExeWin32;
}
else
{
	$svnExecutable = $svnExeLinux;
}

?>