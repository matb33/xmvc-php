<?php

$svnExeWin32 = "mod/SVN/bin/win32/svn.exe";
$svnExeLinux = "mod/SVN/bin/linux/svn";
$svnConfigFolder = "mod/SVN/bin/config/";
$svnWorkingFolder = "mod/SVN/work/";	// Not used by this module's code, here only as an option for you to use as a working folder that already has svn:ignore setup on it

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