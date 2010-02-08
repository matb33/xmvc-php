<?php

$svnExeWin32 = "mod/svn/bin/win32/svn.exe";
$svnExeLinux = "mod/svn/bin/linux/svn";
$svnWorkingFolder = "mod/svn/work/";
$svnConfigFolder = "mod/svn/bin/config/";

$repositoryURL = "https://mcmillan.springloops.com/source/akimbo";
$repositoryPath = "/trunk/web/app/models/";
$repositoryUsername = "akimbo";
$repositoryPassword = "n%9873h$25";

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

