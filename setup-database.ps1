# PRODIGI Database Configuration Wizard
# This script helps you configure the database connection

Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  PRODIGI Database Setup Wizard" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

$configFile = "$PSScriptRoot\config\config.php"

# Method 1: Try using phpMyAdmin (Recommended)
Write-Host "RECOMMENDED: Import database using phpMyAdmin" -ForegroundColor Green
Write-Host ""
Write-Host "Steps:" -ForegroundColor Yellow
Write-Host "1. Open phpMyAdmin in your browser (I'll open it for you)" -ForegroundColor White
Write-Host "2. Click 'Import' tab" -ForegroundColor White
Write-Host "3. Choose file: C:\xampp\htdocs\PRODIGI\database\prodigi_db.sql" -ForegroundColor White
Write-Host "4. Click 'Go' and wait for success message" -ForegroundColor White
Write-Host ""

$openPhpMyAdmin = Read-Host "Do you want to open phpMyAdmin now? (y/n)"
if ($openPhpMyAdmin -eq "y") {
    Start-Process "http://localhost/phpmyadmin"
    Write-Host ""
    Write-Host "phpMyAdmin opened in browser!" -ForegroundColor Green
    Write-Host ""
}

# Wait for user to import
Write-Host "After importing the database, press Enter to continue..." -ForegroundColor Yellow
Read-Host

# Test connection
Write-Host ""
Write-Host "Testing database connection..." -ForegroundColor Green
Write-Host ""

# Ask for MySQL password
Write-Host "Does your MySQL have a password?" -ForegroundColor Yellow
Write-Host "  (If you're using default XAMPP, it's usually NO password)" -ForegroundColor Gray
Write-Host ""
$hasPassword = Read-Host "Does MySQL have a password? (y/n)"

if ($hasPassword -eq "y") {
    $dbPassword = Read-Host "Enter MySQL root password" -AsSecureString
    $dbPasswordPlain = [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($dbPassword))
} else {
    $dbPasswordPlain = ""
}

# Update config file
Write-Host ""
Write-Host "Updating configuration file..." -ForegroundColor Green

try {
    $configContent = Get-Content $configFile -Raw
    
    # Replace password line
    if ($hasPassword -eq "y") {
        $configContent = $configContent -replace "define\('DB_PASS', ''\);", "define('DB_PASS', '$dbPasswordPlain');"
    }
    
    Set-Content $configFile $configContent -NoNewline
    Write-Host "  -> Configuration updated successfully!" -ForegroundColor Green
} catch {
    Write-Host "[ERROR] Failed to update configuration: $_" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please manually edit:" -ForegroundColor Yellow
    Write-Host "  File: $configFile" -ForegroundColor White
    Write-Host "  Change line: define('DB_PASS', ''); " -ForegroundColor White
    Write-Host "  To: define('DB_PASS', 'your_password');" -ForegroundColor White
}

Write-Host ""
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host "  Testing Installation" -ForegroundColor Cyan
Write-Host "=====================================" -ForegroundColor Cyan
Write-Host ""

# Try to open the website
Write-Host "Opening your marketplace..." -ForegroundColor Green
Start-Sleep -Seconds 2
Start-Process "http://localhost/PRODIGI"

Write-Host ""
Write-Host "Your marketplace should now be accessible at:" -ForegroundColor Green
Write-Host "  http://localhost/PRODIGI" -ForegroundColor Cyan
Write-Host ""
Write-Host "Default Admin Login:" -ForegroundColor Yellow
Write-Host "  Username: admin" -ForegroundColor White
Write-Host "  Password: admin123" -ForegroundColor White
Write-Host ""
Write-Host "If you see errors, check:" -ForegroundColor Yellow
Write-Host "  1. Database 'prodigi_db' exists in phpMyAdmin" -ForegroundColor White
Write-Host "  2. MySQL password is correct in config/config.php" -ForegroundColor White
Write-Host "  3. Apache and MySQL are running in XAMPP" -ForegroundColor White
Write-Host ""
Read-Host "Press Enter to exit"
