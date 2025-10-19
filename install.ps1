# PRODIGI Digital Marketplace - PowerShell Installation Script
# This script automates the installation process for Windows/XAMPP

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  PRODIGI Marketplace Installer" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Check if running as Administrator
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
if (-not $isAdmin) {
    Write-Host "[WARNING] Not running as Administrator. Some permissions may fail." -ForegroundColor Yellow
    Write-Host ""
}

# Step 1: Check XAMPP Installation
Write-Host "[1/6] Checking XAMPP installation..." -ForegroundColor Green

$xamppPath = "C:\xampp"
if (-not (Test-Path $xamppPath)) {
    Write-Host "[ERROR] XAMPP not found at $xamppPath" -ForegroundColor Red
    Write-Host "Please install XAMPP from https://www.apachefriends.org/" -ForegroundColor Yellow
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "  -> XAMPP found at $xamppPath" -ForegroundColor White

# Step 2: Check MySQL Service
Write-Host ""
Write-Host "[2/6] Checking MySQL service..." -ForegroundColor Green

$mysqlService = Get-Service -Name "MySQL" -ErrorAction SilentlyContinue
if ($null -eq $mysqlService -or $mysqlService.Status -ne "Running") {
    Write-Host "[WARNING] MySQL service is not running!" -ForegroundColor Yellow
    Write-Host "Please start MySQL from XAMPP Control Panel" -ForegroundColor Yellow
    Write-Host ""
    $continue = Read-Host "Do you want to continue anyway? (y/n)"
    if ($continue -ne "y") {
        exit 1
    }
} else {
    Write-Host "  -> MySQL is running" -ForegroundColor White
}

# Step 3: Check Apache Service
Write-Host ""
Write-Host "[3/6] Checking Apache service..." -ForegroundColor Green

$apacheService = Get-Service -Name "Apache*" -ErrorAction SilentlyContinue | Select-Object -First 1
if ($null -eq $apacheService -or $apacheService.Status -ne "Running") {
    Write-Host "[WARNING] Apache service is not running!" -ForegroundColor Yellow
    Write-Host "Please start Apache from XAMPP Control Panel" -ForegroundColor Yellow
    Write-Host ""
    $continue = Read-Host "Do you want to continue anyway? (y/n)"
    if ($continue -ne "y") {
        exit 1
    }
} else {
    Write-Host "  -> Apache is running" -ForegroundColor White
}

# Step 4: Import Database
Write-Host ""
Write-Host "[4/6] Importing database..." -ForegroundColor Green

$mysqlExe = "$xamppPath\mysql\bin\mysql.exe"
$sqlFile = "$PSScriptRoot\database\prodigi_db.sql"

if (-not (Test-Path $mysqlExe)) {
    Write-Host "[ERROR] MySQL executable not found at $mysqlExe" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

if (-not (Test-Path $sqlFile)) {
    Write-Host "[ERROR] SQL file not found at $sqlFile" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "  -> Importing from $sqlFile" -ForegroundColor White

try {
    # Create database
    Write-Host "  -> Creating database..." -ForegroundColor White
    & $mysqlExe -u root --execute="DROP DATABASE IF EXISTS prodigi_db; CREATE DATABASE prodigi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    
    if ($LASTEXITCODE -ne 0) {
        throw "Failed to create database"
    }
    
    # Import SQL file
    Write-Host "  -> Importing SQL file..." -ForegroundColor White
    $sqlContent = Get-Content $sqlFile -Raw
    $sqlContent | & $mysqlExe -u root prodigi_db
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host "  -> Database imported successfully!" -ForegroundColor Green
    } else {
        Write-Host "[ERROR] Database import failed with exit code $LASTEXITCODE" -ForegroundColor Red
        Write-Host "Please check your MySQL configuration" -ForegroundColor Yellow
        Read-Host "Press Enter to exit"
        exit 1
    }
} catch {
    Write-Host "[ERROR] Failed to import database: $_" -ForegroundColor Red
    Read-Host "Press Enter to exit"
    exit 1
}

# Step 5: Set Directory Permissions
Write-Host ""
Write-Host "[5/6] Setting directory permissions..." -ForegroundColor Green

$directories = @(
    "$PSScriptRoot\uploads",
    "$PSScriptRoot\uploads\products",
    "$PSScriptRoot\uploads\stores",
    "$PSScriptRoot\uploads\users",
    "$PSScriptRoot\uploads\files"
)

foreach ($dir in $directories) {
    if (Test-Path $dir) {
        Write-Host "  -> Setting permissions for $dir" -ForegroundColor White
        try {
            # Grant full control to Users group
            $acl = Get-Acl $dir
            $permission = "Users", "FullControl", "ContainerInherit,ObjectInherit", "None", "Allow"
            $accessRule = New-Object System.Security.AccessControl.FileSystemAccessRule $permission
            $acl.SetAccessRule($accessRule)
            Set-Acl $dir $acl
        } catch {
            Write-Host "[WARNING] Could not set permissions for $dir : $_" -ForegroundColor Yellow
        }
    } else {
        Write-Host "[WARNING] Directory not found: $dir" -ForegroundColor Yellow
    }
}

Write-Host "  -> Permissions set successfully!" -ForegroundColor Green

# Step 6: Verify Configuration
Write-Host ""
Write-Host "[6/6] Verifying configuration..." -ForegroundColor Green

$configFile = "$PSScriptRoot\config\config.php"
if (Test-Path $configFile) {
    Write-Host "  -> Configuration file found" -ForegroundColor White
} else {
    Write-Host "[ERROR] Configuration file not found at $configFile" -ForegroundColor Red
}

# Installation Complete
Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  Installation Complete!" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "Default Admin Credentials:" -ForegroundColor Yellow
Write-Host "  Username: admin" -ForegroundColor White
Write-Host "  Password: admin123" -ForegroundColor White
Write-Host ""
Write-Host "Your marketplace is ready at:" -ForegroundColor Green
Write-Host "  http://localhost/PRODIGI" -ForegroundColor Cyan
Write-Host ""

# Ask to open browser
$openBrowser = Read-Host "Do you want to open the application in browser? (y/n)"
if ($openBrowser -eq "y") {
    Start-Process "http://localhost/PRODIGI"
}

Write-Host ""
Write-Host "Thank you for using PRODIGI!" -ForegroundColor Green
Write-Host ""
Read-Host "Press Enter to exit"
