@echo off
set HOST=%1
set PORT=%2
if "%HOST%"=="" set HOST=127.0.0.1
if "%PORT%"=="" set PORT=8080
php -S %HOST%:%PORT% -t public public/router.php
