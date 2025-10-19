@echo off
echo ========================================
echo PRODIGI - Installation Script
echo ========================================
echo.

REM Check if MySQL is running
echo [1/5] Checking MySQL status...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo MySQL is running... OK
) else (
    echo ERROR: MySQL is not running!
    echo Please start MySQL from XAMPP Control Panel
    pause
    exit /b 1
)

REM Check if Apache is running
echo [2/5] Checking Apache status...
tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL | find /I /N "httpd.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo Apache is running... OK
) else (
    echo ERROR: Apache is not running!
    echo Please start Apache from XAMPP Control Panel
    pause
    exit /b 1
)

REM Import database
echo [3/5] Importing database...
echo This may take a moment...
cd /d C:\xampp\mysql\bin
mysql -u root -e "CREATE DATABASE IF NOT EXISTS prodigi_db;"
mysql -u root prodigi_db < "C:\xampp\htdocs\PRODIGI\database\prodigi_db.sql"
if %ERRORLEVEL% == 0 (
    echo Database imported successfully!
) else (
    echo WARNING: Database import may have issues
)

REM Set folder permissions
echo [4/5] Setting folder permissions...
icacls "C:\xampp\htdocs\PRODIGI\uploads" /grant Everyone:F /T >nul 2>&1
echo Permissions set!

REM Open application in browser
echo [5/5] Opening PRODIGI in browser...
timeout /t 2 /nobreak >nul
start http://localhost/PRODIGI

echo.
echo ========================================
echo Installation Complete!
echo ========================================
echo.
echo Application URL: http://localhost/PRODIGI
echo Admin Panel: http://localhost/PRODIGI/admin/dashboard.php
echo.
echo Default Admin Credentials:
echo Username: admin
echo Password: admin123
echo.
echo IMPORTANT: Change admin password after first login!
echo.
echo Setup Guide: See SETUP_GUIDE.md
echo Documentation: See README.md
echo.
pause
