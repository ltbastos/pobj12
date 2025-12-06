@echo off
cd /d "%~dp0"
php -S localhost:8081 -t public
pause