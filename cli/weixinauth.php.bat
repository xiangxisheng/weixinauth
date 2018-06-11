echo off
cd /d %~dp0
cls
for %%i in (%0) do (set "name=%%~ni") 
title %name%
:start
echo [Start] %name%...
php %name%
echo [END] waiting try again...
CALL :delay 10
goto start
pause
EXIT
REM =====================================
:delay
for /L %%i in (%1,-1,1) do (
CALL :echo "%%i "
choice /t 1 /d y /n >nul
)
echo 0
goto :eof
:echo
set /p="%~1"<nul
goto :eof
