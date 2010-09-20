@echo off
setlocal

set URLFILE=%~1
set WEBROOT=%~2
set OUTPUTPATH=%~3
set HTTPHOST=%~4
set TESTINGPATH=%~5

REM ---------------------------------------------------------------------------
echo PRESS ENTER 3 TIMES AND JUMP UP AND DOWN TO CONFIRM THAT YOU WANT TO BEGIN FLATTENING
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

set LINECOUNT=0
set NOCOUNT=0;
for /f %%i in (!URLFILE!) do (
	set /A LINECOUNT+=1
	echo ### ...on URL: %%i
	echo.

	!WEBROOT!\mod\Flattener\bin\win32\wget-1.12.exe --tries=1 --level=inf --recursive --html-extension --force-directories --no-parent --reject="*vparam=*","*video=*" --verbose %%i

	REM choice /m "Would you like to retrieve the following URL with wget:%%i"
	REM if !ERRORLEVEL!==1 !WEBROOT!\mod\Flattener\bin\win32\wget-1.12.exe --tries=1 --level=inf --recursive --html-extension --force-directories --no-parent --reject="*vparam=*","*video=*" --verbose %%i
	REM if !ERRORLEVEL!==2 ( 
	REM 	echo Skipping URL:%%i
	REM	set /A NOCOUNT+=1
	REM )

	echo.
)

if %LINECOUNT%==%NOCOUNT% goto :EOF

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
xcopy "!WEBROOT!\..\src\EdcVideoHandler\EdcVideoHandler\bin\release\*.dll" "!OUTPUTPATH!\onlineguide\bin\" /d /e /c /i /g /r /y
xcopy "!WEBROOT!\..\src\EdcVideoHandler\EdcVideoHandler\web.config" "!OUTPUTPATH!\onlineguide\" /d /e /c /i /g /r /y
xcopy "!WEBROOT!\..\src\EdcVideoHandler\EdcVideoHandler\web.config.iis6" "!OUTPUTPATH!\onlineguide\" /d /e /c /i /g /r /y

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