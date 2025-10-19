# ⚡ Quick Start Guide - PRODIGI Marketplace

## 🚀 5-Minute Installation

### Step 1: Start XAMPP Services (REQUIRED!)

1. Open **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`)
2. Click **Start** next to **Apache** ✅
3. Click **Start** next to **MySQL** ✅
4. Both should show **green** status

![XAMPP Control Panel](https://i.imgur.com/example.png)

**⚠️ IMPORTANT:** Do NOT proceed until both services are running!

---

### Step 2: Import Database

**Option A: Using phpMyAdmin (Easiest)**

1. Open browser → `http://localhost/phpmyadmin`
2. Click **"Import"** tab at the top
3. Click **"Choose File"** → Select: `C:\xampp\htdocs\PRODIGI\database\prodigi_db.sql`
4. Scroll down → Click **"Go"**
5. Wait for success message ✅

**Option B: Using MySQL Command**

```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root -e "DROP DATABASE IF EXISTS prodigi_db; CREATE DATABASE prodigi_db;"
.\mysql.exe -u root prodigi_db < ..\..\htdocs\PRODIGI\database\prodigi_db.sql
```

---

### Step 3: Access Your Marketplace

Open browser and visit: **http://localhost/PRODIGI**

---

## 🔑 Default Login

**Admin Account:**
- URL: http://localhost/PRODIGI/login.php
- Username: `admin`
- Password: `admin123`

**⚠️ Change password after first login!**

---

## ✅ Quick Test Checklist

Visit these URLs to verify everything works:

- [ ] **Homepage:** http://localhost/PRODIGI
- [ ] **Products:** http://localhost/PRODIGI/products.php
- [ ] **Login:** http://localhost/PRODIGI/login.php
- [ ] **Register:** http://localhost/PRODIGI/register.php
- [ ] **Admin Panel:** http://localhost/PRODIGI/admin/dashboard.php

---

## 🐛 Troubleshooting

### Problem: "Apache won't start"
**Solutions:**
- Close Skype (it uses port 80)
- Stop IIS if running
- Check port 80 isn't in use: `netstat -ano | findstr :80`

### Problem: "MySQL won't start"
**Solutions:**
- Check if MySQL is already running as Windows service
- Stop other MySQL instances from Task Manager
- Check port 3306: `netstat -ano | findstr :3306`

### Problem: "Cannot import database"
**Solutions:**
- Make sure MySQL is **running** (green in XAMPP)
- Use phpMyAdmin method instead
- Check file exists: `C:\xampp\htdocs\PRODIGI\database\prodigi_db.sql`

### Problem: "Page not found"
**Solutions:**
- Verify folder is at: `C:\xampp\htdocs\PRODIGI`
- Apache must be running (green)
- Try: http://localhost/PRODIGI/index.php

### Problem: "Database connection error"
**Solutions:**
- MySQL must be running
- Check database was imported successfully
- Verify database name is `prodigi_db` in phpMyAdmin

---

## 🎯 What's Next?

After installation:

1. **Login as admin** → http://localhost/PRODIGI/login.php
2. **Register a user** → http://localhost/PRODIGI/register.php
3. **Create a store** → Request seller account
4. **Upload products** → Add your first digital product
5. **Test purchases** → Buy a product to test the flow

---

## 📚 Full Documentation

- **Complete Guide:** `README.md`
- **Features List:** `FEATURES.md`
- **Detailed Install:** `INSTALL.md`
- **Project Summary:** `PROJECT_SUMMARY.md`

---

## 🎉 You're Ready!

Your digital marketplace is installed and ready to use!

**Frontend:** http://localhost/PRODIGI  
**Admin Panel:** http://localhost/PRODIGI/admin/dashboard.php

**Happy Selling! 🚀**
