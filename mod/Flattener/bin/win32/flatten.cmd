@echo off
setlocal

set URLFILE=%~1
set WEBROOT=%~2
set OUTPUTPATH=%~3
set HTTPHOST=%~4
set TESTINGPATH=%~5

REM ---------------------------------------------------------------------------
echo PRESS ENTER 3 TIMES TO CONFIRM THAT YOU WANT TO BEGIN FLATTENING
echo.
pause
pause
pause

echo.
REM ---------------------------------------------------------------------------
echo ### Attempting to remove output folder: !OUTPUTPATH!
echo.

if exist !OUTPUTPATH! (
	echo Found, removing.
	rd /s /q !OUTPUTPATH!
) else (
	echo Not found, skip.
)

REM ---------------------------------------------------------------------------
echo.
echo ### Removing cache files
echo.

del /f !WEBROOT!\app\inc\cache\*.css
del /f !WEBROOT!\app\inc\cache\*.js

REM ---------------------------------------------------------------------------
echo.
echo ### Executing wget on URL's found in !URLFILE!...
echo.

for /f %%i in (!URLFILE!) do (
	echo ### ...on URL: %%i
	echo.
	!WEBROOT!\mod\Flattener\bin\win32\wget-1.12.exe --level=inf --recursive --html-extension --force-directories --no-parent --reject="*vparam=*","*video=*" --verbose %%i
	echo.
)

REM ---------------------------------------------------------------------------
echo ### Renaming !HTTPHOST! to !OUTPUTPATH!
echo.

move "!HTTPHOST!" "!OUTPUTPATH!"

REM ---------------------------------------------------------------------------
echo.
echo ### HARD-CODED Copying inc folders... abstract this somehow...
echo.

xcopy "!WEBROOT!\app\inc\*.*" "!OUTPUTPATH!\onlineguide\inc\" /d /e /c /i /g /r /y
xcopy "!WEBROOT!\mod\WebCommon\inc\*.*" "!OUTPUTPATH!\onlineguide\WebCommon\inc\" /d /e /c /i /g /r /y

REM ---------------------------------------------------------------------------
echo.
echo ### Copying to testing server
echo.

rd /s /q !TESTINGPATH!
xcopy "!OUTPUTPATH!\*.*" "!TESTINGPATH!" /d /e /c /i /g /r /y

REM ---------------------------------------------------------------------------
echo.
echo ### Flattening process completed
echo.

pause

endlocal