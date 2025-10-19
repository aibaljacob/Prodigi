# 🚀 PRODIGI - Quick Setup Guide

## ⚡ 5-Minute Local Setup

### Step 1: Start XAMPP
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL**
3. Ensure both are running (green indicators)

### Step 2: Import Database
1. Open browser: `http://localhost/phpmyadmin`
2. Click **"New"** → Create database: `prodigi_db`
3. Click **"Import"** tab
4. Choose file: `C:\xampp\htdocs\PRODIGI\database\prodigi_db.sql`
5. Click **"Go"**
6. Wait for success message ✅

### Step 3: Access Application
Open browser and visit:
```
http://localhost/PRODIGI
```

### Step 4: Login as Admin
```
Username: admin
Password: admin123
```

## ✅ Verification Checklist

- [ ] Homepage loads successfully
- [ ] Can login with admin credentials
- [ ] Admin dashboard displays stats
- [ ] Can register new user
- [ ] File uploads work (check permissions)
- [ ] Database connection successful

## 🔧 Troubleshooting

### Problem: Database Connection Error
**Solution:**
1. Check MySQL is running in XAMPP
2. Verify `config/config.php` database settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prodigi_db');
```

### Problem: Blank White Page
**Solution:**
1. Enable error display in `config/config.php`:
```php
ini_set('display_errors', 1);
error_reporting(E_ALL);
```
2. Check `C:\xampp\apache\logs\error.log`

### Problem: File Upload Fails
**Solution:**
1. Right-click `uploads` folder → Properties → Uncheck "Read-only"
2. Or set permissions via command:
```cmd
icacls "C:\xampp\htdocs\PRODIGI\uploads" /grant Everyone:F /T
```

### Problem: CSS/JS Not Loading
**Solution:**
1. Clear browser cache (Ctrl + Shift + Del)
2. Check file paths in `config/config.php`
3. Verify `assets` folder exists

## 📝 Default Test Data

### Admin Account
```
URL: http://localhost/PRODIGI/admin/dashboard.php
Username: admin
Password: admin123
```

### Categories Installed
- Graphics
- Templates
- Audio
- Video
- Ebooks
- Software

### Sample Workflow
1. **Register** new user → `http://localhost/PRODIGI/register.php`
2. **Become Seller** → Request seller account
3. **Admin Approves** → Login as admin, approve seller
4. **Create Store** → Seller creates store (pending approval)
5. **Admin Approves Store** → Approve from admin panel
6. **Upload Product** → Seller uploads product
7. **Admin Approves Product** → Product goes live
8. **Buy Product** → Test complete purchase flow

## 🎨 Customization

### Change Colors
Edit `assets/css/style.css`:
```css
:root {
    --primary-color: #4B6EF5;    /* Your primary color */
    --secondary-color: #00C2A8;  /* Your secondary color */
    --accent-color: #F5B400;     /* Your accent color */
}
```

### Change Logo
1. Replace logo text in `views/includes/header.php`
2. Or add image: `<img src="assets/images/logo.png" alt="PRODIGI">`

### Commission Settings
1. Login as admin
2. Go to Settings
3. Change "Commission Percentage" (default: 10%)

## 🔐 Security Checklist (Production)

- [ ] Change admin password immediately
- [ ] Update `RAZORPAY_KEY_ID` and `RAZORPAY_KEY_SECRET`
- [ ] Set `APP_ENV` to `'production'` in config
- [ ] Enable HTTPS
- [ ] Uncomment HTTPS redirect in `.htaccess`
- [ ] Disable error display:
```php
ini_set('display_errors', 0);
error_reporting(0);
```
- [ ] Set strong database password
- [ ] Backup database regularly

## 📱 Mobile Testing

Test on mobile devices:
1. Find your local IP: `ipconfig` (look for IPv4)
2. Update `config.php`:
```php
define('APP_URL', 'http://YOUR_IP/PRODIGI');
```
3. Access from mobile: `http://YOUR_IP/PRODIGI`

## 🌐 AWS Deployment (Production)

### Quick Deploy Steps
1. Launch EC2 instance (t2.micro)
2. Install LAMP stack
3. Upload files via SCP/FTP
4. Import database
5. Configure SSL certificate
6. Update config.php with production URL

**Detailed guide**: See README.md → AWS Deployment section

## 📊 Performance Tips

### Enable Caching
Add to `.htaccess`:
```apache
<IfModule mod_expires.c>
    ExpiresActive On
    ExpiresDefault "access plus 1 month"
</IfModule>
```

### Optimize Images
- Use WebP format for images
- Compress images before upload
- Max recommended: 1920x1080px

### Database Optimization
Run periodically:
```sql
OPTIMIZE TABLE products;
OPTIMIZE TABLE transactions;
```

## 🆘 Getting Help

### Check Logs
- **Apache Error Log**: `C:\xampp\apache\logs\error.log`
- **PHP Error Log**: `C:\xampp\php\logs\php_error_log`
- **MySQL Error Log**: `C:\xampp\mysql\data\mysql_error.log`

### Common Error Messages

**"Access denied for user"**
→ Check database credentials

**"Class not found"**
→ Check autoloader in `config/config.php`

**"Permission denied"**
→ Fix folder permissions

**"Headers already sent"**
→ Remove whitespace before `<?php` tags

## 📞 Support

- Email: support@prodigi.com
- Documentation: README.md
- Check error logs first!

## 🎉 You're All Set!

Visit: `http://localhost/PRODIGI`

Happy coding! 🚀
