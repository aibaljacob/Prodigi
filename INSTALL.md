# 🚀 Installation Guide - PRODIGI Marketplace

## Quick Start (Choose One Method)

### Method 1: PowerShell (Recommended for Windows)
```powershell
cd c:\xampp\htdocs\PRODIGI
.\install.ps1
```

### Method 2: Batch File
```cmd
cd c:\xampp\htdocs\PRODIGI
.\install.bat
```

### Method 3: Manual Installation
Follow the steps below if automatic installation fails.

---

## Prerequisites

✅ **XAMPP Installed** (Apache + MySQL)  
✅ **Apache Running** (Port 80)  
✅ **MySQL Running** (Port 3306)  
✅ **PHP 7.4 or higher**  

---

## 📋 Manual Installation Steps

### Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** (should show green)
3. Start **MySQL** (should show green)

### Step 2: Create Database

**Option A: Using phpMyAdmin**
1. Open browser and go to `http://localhost/phpmyadmin`
2. Click "New" to create database
3. Database name: `prodigi_db`
4. Collation: `utf8mb4_unicode_ci`
5. Click "Create"
6. Click "Import" tab
7. Choose file: `c:\xampp\htdocs\PRODIGI\database\prodigi_db.sql`
8. Click "Go"

**Option B: Using MySQL Command Line**
```bash
cd c:\xampp\mysql\bin
mysql -u root
```
```sql
DROP DATABASE IF EXISTS prodigi_db;
CREATE DATABASE prodigi_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE prodigi_db;
SOURCE c:/xampp/htdocs/PRODIGI/database/prodigi_db.sql;
EXIT;
```

### Step 3: Set Directory Permissions

Right-click on these folders → Properties → Security → Edit → Add "Users" with Full Control:
- `c:\xampp\htdocs\PRODIGI\uploads`
- `c:\xampp\htdocs\PRODIGI\uploads\products`
- `c:\xampp\htdocs\PRODIGI\uploads\stores`
- `c:\xampp\htdocs\PRODIGI\uploads\users`
- `c:\xampp\htdocs\PRODIGI\uploads\files`

### Step 4: Verify Configuration

Open `c:\xampp\htdocs\PRODIGI\config\config.php` and verify:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prodigi_db');
```

### Step 5: Access Application

Open browser and visit: **http://localhost/PRODIGI**

---

## 🔑 Default Credentials

**Admin Login:**
- Username: `admin`
- Password: `admin123`

**⚠️ IMPORTANT:** Change the admin password after first login!

---

## 🐛 Troubleshooting

### Issue: "install.ps1 cannot be loaded"
**Solution:** Enable script execution in PowerShell:
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Issue: "Access Denied" when importing database
**Solution:** Run PowerShell or Command Prompt as Administrator

### Issue: "Error establishing database connection"
**Solutions:**
1. Verify MySQL is running in XAMPP Control Panel
2. Check database credentials in `config/config.php`
3. Ensure database `prodigi_db` exists
4. Test connection:
   ```bash
   cd c:\xampp\mysql\bin
   mysql -u root
   SHOW DATABASES;
   ```

### Issue: Apache won't start - Port 80 in use
**Solutions:**
1. Check if Skype is using port 80 (disable it)
2. Check if IIS is running (stop it)
3. Change Apache port in `c:\xampp\apache\conf\httpd.conf`

### Issue: MySQL won't start - Port 3306 in use
**Solutions:**
1. Check if MySQL is already running as Windows service
2. Check Task Manager for other MySQL instances
3. Change MySQL port in `c:\xampp\mysql\bin\my.ini`

### Issue: "Call to undefined function mysqli_connect()"
**Solution:** Enable mysqli extension in `php.ini`:
```ini
extension=mysqli
extension=pdo_mysql
```

### Issue: File upload not working
**Solutions:**
1. Check directory permissions (Step 3)
2. Verify `upload_max_filesize` in `php.ini` (should be 100M)
3. Verify `post_max_size` in `php.ini` (should be 100M)
4. Restart Apache after changing php.ini

### Issue: Blank page after installation
**Solutions:**
1. Enable error display in `php.ini`:
   ```ini
   display_errors = On
   error_reporting = E_ALL
   ```
2. Check Apache error logs: `c:\xampp\apache\logs\error.log`
3. Check PHP error logs: `c:\xampp\php\logs\php_error_log`

### Issue: CSS/JS not loading
**Solutions:**
1. Clear browser cache (Ctrl + Shift + Delete)
2. Check if `.htaccess` is working (requires mod_rewrite)
3. Verify file paths in `config/config.php`

---

## 🔧 Configuration Options

### Change Database Password
If your MySQL has a password, edit `config/config.php`:
```php
define('DB_PASS', 'your_mysql_password');
```

### Change Upload Limits
Edit `c:\xampp\php\php.ini`:
```ini
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
memory_limit = 256M
```

### Enable Mod Rewrite (for clean URLs)
Edit `c:\xampp\apache\conf\httpd.conf`:
```apache
# Uncomment this line:
LoadModule rewrite_module modules/mod_rewrite.so

# Change AllowOverride None to All:
<Directory "C:/xampp/htdocs">
    AllowOverride All
    Require all granted
</Directory>
```

---

## 📧 Email Configuration (Optional)

To enable email notifications, edit `config/config.php`:

```php
// SMTP Configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'noreply@prodigi.com');
define('SMTP_NAME', 'PRODIGI Marketplace');
```

---

## 🔐 Security Hardening (Production)

Before deploying to production:

1. **Change Database Password:**
   ```sql
   ALTER USER 'root'@'localhost' IDENTIFIED BY 'strong_password';
   ```

2. **Change Admin Password:**
   - Login as admin
   - Go to Profile → Change Password

3. **Update Razorpay Keys:**
   - Edit `config/config.php`
   - Replace test keys with live keys

4. **Enable HTTPS:**
   - Get SSL certificate (Let's Encrypt)
   - Update BASE_URL to `https://`

5. **Disable Error Display:**
   ```ini
   display_errors = Off
   log_errors = On
   ```

6. **Set Proper File Permissions:**
   - Files: 644
   - Directories: 755
   - config.php: 640

---

## 📊 Verify Installation

### Database Check
```sql
mysql -u root prodigi_db
SHOW TABLES;
-- Should show 13 tables
SELECT COUNT(*) FROM users;
-- Should return 1 (admin user)
SELECT COUNT(*) FROM categories;
-- Should return 6 categories
```

### File Structure Check
Ensure these directories exist:
- ✅ `classes/`
- ✅ `config/`
- ✅ `database/`
- ✅ `views/`
- ✅ `assets/css/`
- ✅ `assets/js/`
- ✅ `assets/images/`
- ✅ `uploads/`
- ✅ `api/`
- ✅ `admin/`

### Functionality Check
1. ✅ Homepage loads
2. ✅ Login page works
3. ✅ Admin dashboard accessible
4. ✅ Registration form works
5. ✅ Products page loads
6. ✅ Cart functions work

---

## 🎯 Next Steps After Installation

1. **Login as Admin:** `http://localhost/PRODIGI/login.php`
2. **Configure Settings:** Admin → Settings
3. **Add Categories:** (Already added via SQL)
4. **Update Site Info:** Edit templates with your branding
5. **Configure Razorpay:** Get live API keys
6. **Test User Flow:** Register → Create Store → Upload Product → Purchase
7. **Review Security:** Change passwords, enable HTTPS
8. **Deploy to Production:** Follow AWS EC2 guide in README.md

---

## 📞 Need Help?

If you encounter issues not covered here:

1. Check Apache error log: `c:\xampp\apache\logs\error.log`
2. Check PHP error log: `c:\xampp\php\logs\php_error_log`
3. Enable debugging in `config/config.php`
4. Review `README.md` for detailed documentation
5. Check `FEATURES.md` for implemented features

---

## 🎉 Installation Complete!

Your PRODIGI marketplace is ready to use!

- **Frontend:** http://localhost/PRODIGI
- **Admin Panel:** http://localhost/PRODIGI/admin
- **phpMyAdmin:** http://localhost/phpmyadmin

**Happy Selling! 🚀**
