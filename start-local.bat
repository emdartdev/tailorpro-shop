@echo off
title TailorPro Local Server Launcher
echo ===================================================
echo Starting TailorPro Application Services...
echo ===================================================

:: Check if MySQL is running
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL is already running.
) else (
    echo [*] Starting MySQL from XAMPP...
    start /B "" "C:\xampp\mysql\bin\mysqld.exe" --defaults-file="C:\xampp\mysql\bin\my.ini" --standalone
    timeout /t 3 /nobreak >nul
)

:: Clean any stale hot reload file
if exist "public\hot" del "public\hot"

:: Start Laravel Backend Server
echo [*] Starting Laravel API Server on http://127.0.0.1:8000 ...
start "TailorPro - Laravel Server" php artisan serve --host=127.0.0.1 --port=8000

:: Start Vite Frontend Dev Server
echo [*] Starting Vite Frontend Server on http://127.0.0.1:5173 ...
start "TailorPro - Vite Dev" npm run dev

echo ===================================================
echo [SUCCESS] Application is now live!
echo Open your browser at: http://127.0.0.1:8000
echo ===================================================
timeout /t 3 >nul
start http://127.0.0.1:8000
