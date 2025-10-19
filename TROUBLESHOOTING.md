# 🔧 PRODIGI - Common Issues & Solutions

## ✅ Installation Complete!

If you can see the PRODIGI homepage, **congratulations!** Your marketplace is working!

---

## 🐛 Common Errors & Fixes

### 1. ❌ "Access denied for user 'root'@'localhost'"

**Problem:** MySQL password mismatch

**Solution:**
```powershell
cd C:\xampp\htdocs\PRODIGI
.\setup-database.ps1
```

Or manually edit `config/config.php`:
```php
define('DB_PASS', 'your_mysql_password');
```

---

### 2. ❌ "Database connection failed"

**Solutions:**

**Check 1: MySQL is Running**
- Open XAMPP Control Panel
- MySQL should show **green** status
- If not, click Start

**Check 2: Database Exists**
- Open http://localhost/phpmyadmin
- Look for database named `prodigi_db`
- If missing, import: `database/prodigi_db.sql`

**Check 3: Password is Correct**
- Edit `config/config.php`
- Make sure `DB_PASS` matches your MySQL password

---

### 3. ❌ "Plugin caching_sha2_password could not be loaded"

**Problem:** MySQL 8+ authentication issue

**Solution A: Use phpMyAdmin (Easiest)**
1. Open http://localhost/phpmyadmin
2. Click "Import" tab
3. Import `database/prodigi_db.sql`

**Solution B: Change MySQL authentication**
1. Open phpMyAdmin
2. Click "SQL" tab
3. Run:
```sql
ALTER USER 'root'@'localhost' IDENTIFIED WITH mysql_native_password BY '';
FLUSH PRIVILEGES;
```

---

### 4. ❌ "Page not found" or "404 Error"

**Solutions:**

**Check 1: Apache is Running**
- XAMPP Control Panel → Apache should be green

**Check 2: Correct URL**
- Use: `http://localhost/PRODIGI`
- NOT: `http://localhost/` (missing PRODIGI)

**Check 3: Folder Location**
- Files should be at: `C:\xampp\htdocs\PRODIGI\`
- NOT: `C:\xampp\htdocs\PRODIGI\PRODIGI\`

---

### 5. ❌ "Warning: session_start()" errors

**Solution:**
Create session directory:
```powershell
mkdir C:\xampp\tmp
icacls C:\xampp\tmp /grant Users:F
```

---

### 6. ❌ CSS/JavaScript not loading (plain HTML page)

**Solutions:**

**Clear Browser Cache:**
- Press `Ctrl + Shift + Delete`
- Clear cache
- Refresh page with `Ctrl + F5`

**Check .htaccess:**
- File should exist: `.htaccess`
- Enable mod_rewrite in Apache

---

### 7. ❌ "Class 'Database' not found"

**Solution:**
Check `config/config.php` is loading first:
```php
<?php
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';
```

---

### 8. ❌ File upload not working

**Solutions:**

**Check 1: Folder Permissions**
Right-click folders → Properties → Security → Add Users with Full Control:
- `uploads/`
- `uploads/products/`
- `uploads/stores/`
- `uploads/files/`
- `uploads/users/`

**Check 2: PHP Settings**
Edit `C:\xampp\php\php.ini`:
```ini
upload_max_filesize = 100M
post_max_size = 100M
```
Restart Apache after changes.

---

### 9. ❌ "Cannot modify header information"

**Solution:**
Remove any spaces or text before `<?php` in files.

---

### 10. ❌ "Allowed memory size exhausted"

**Solution:**
Edit `C:\xampp\php\php.ini`:
```ini
memory_limit = 256M
```
Restart Apache.

---

## 🔍 How to Check Errors

### Enable Error Display

Edit `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Check Apache Error Log

Open: `C:\xampp\apache\logs\error.log`

### Check PHP Error Log

Open: `C:\xampp\php\logs\php_error_log`

---

## 📊 Verify Installation

### Database Check

Visit phpMyAdmin → Click `prodigi_db`:

**Should have these tables:**
- ✅ users (with 1 admin user)
- ✅ categories (with 6 categories)
- ✅ stores
- ✅ products
- ✅ transactions
- ✅ shopping_cart
- ✅ reviews
- ✅ product_files
- ✅ payouts
- ✅ notifications
- ✅ activity_logs
- ✅ admin_settings
- ✅ wishlist

**Total: 13 tables**

### File Structure Check

These files should exist:
- ✅ `index.php`
- ✅ `login.php`
- ✅ `register.php`
- ✅ `products.php`
- ✅ `config/config.php`
- ✅ `classes/Database.php`
- ✅ `assets/css/style.css`
- ✅ `assets/js/main.js`
- ✅ `admin/dashboard.php`

---

## 🧪 Test Functionality

### Test 1: Homepage Loads
Visit: http://localhost/PRODIGI
- Should see hero section
- Should see categories
- Should see navigation

### Test 2: Admin Login
1. Visit: http://localhost/PRODIGI/login.php
2. Username: `admin`
3. Password: `admin123`
4. Should redirect to admin dashboard

### Test 3: User Registration
1. Visit: http://localhost/PRODIGI/register.php
2. Fill form and submit
3. Should create account and login

### Test 4: Products Page
Visit: http://localhost/PRODIGI/products.php
- Should see product filters
- Should see category sidebar
- Should load without errors

---

## 🚀 Performance Issues

### Slow Loading?

**Solution 1: Enable OpCache**
Edit `C:\xampp\php\php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
```

**Solution 2: Add Database Indexes**
Already included in `prodigi_db.sql`!

**Solution 3: Enable Compression**
Already configured in `.htaccess`!

---

## 🔐 Security Checklist

Before going live:

- [ ] Change admin password
- [ ] Update Razorpay keys (live mode)
- [ ] Enable HTTPS/SSL
- [ ] Set strong MySQL password
- [ ] Disable error display in production
- [ ] Set proper file permissions
- [ ] Enable rate limiting
- [ ] Configure backup system

---

## 📞 Still Having Issues?

### Check These Files:

1. **Apache Error Log:** `C:\xampp\apache\logs\error.log`
2. **PHP Error Log:** `C:\xampp\php\logs\php_error_log`
3. **Configuration:** `config/config.php`

### Common Mistakes:

❌ MySQL not started  
❌ Wrong folder location  
❌ Database not imported  
❌ Wrong password in config  
❌ Browser cache not cleared  

### Quick Reset:

```powershell
# Stop services
# Open XAMPP → Stop Apache & MySQL

# Delete database
# phpMyAdmin → Drop prodigi_db

# Re-import
# phpMyAdmin → Import → prodigi_db.sql

# Start services
# XAMPP → Start Apache & MySQL

# Clear browser cache
# Ctrl + Shift + Delete

# Try again
# http://localhost/PRODIGI
```

---

## ✅ Everything Working?

**Next Steps:**

1. ✅ Login as admin
2. ✅ Change admin password
3. ✅ Customize homepage content
4. ✅ Add real categories/products
5. ✅ Configure payment gateway
6. ✅ Test complete user flow
7. ✅ Deploy to production (AWS EC2)

---

## 🎉 Success!

If everything is working, your PRODIGI marketplace is ready to use!

**Admin Panel:** http://localhost/PRODIGI/admin/dashboard.php  
**Frontend:** http://localhost/PRODIGI  

**Happy Selling! 🚀**

---

**Last Updated:** October 19, 2025  
**Version:** 1.0.0
