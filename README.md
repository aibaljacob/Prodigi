# 🚀 PRODIGI - Digital Products Marketplace

![PRODIGI Banner](https://via.placeholder.com/1200x300/4B6EF5/FFFFFF?text=PRODIGI+-+Digital+Products+Marketplace)

A comprehensive digital products marketplace built with **PHP OOP**, **MySQL**, **HTML5**, **CSS3**, and **JavaScript**. Inspired by platforms like Etsy and Gumroad, PRODIGI allows users to buy and sell digital products with a complete admin management system.

---

## 📋 Table of Contents
- [Features](#-features)
- [Technology Stack](#-technology-stack)
- [Project Structure](#-project-structure)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Usage](#-usage)
- [AWS Deployment](#-aws-deployment)
- [Security Features](#-security-features)
- [API Documentation](#-api-documentation)
- [Screenshots](#-screenshots)
- [Contributing](#-contributing)
- [License](#-license)

---

## ✨ Features

### 🛒 **For Buyers**
- Browse digital products by category
- Advanced search and filtering (price, ratings, tags)
- Shopping cart functionality
- Secure payment via Razorpay
- Instant download after purchase
- Download history and re-download options
- Product reviews and ratings
- User profile management

### 🏪 **For Sellers**
- Create and customize your own store
- Upload multiple digital products
- Product inventory management
- Real-time sales analytics
- Earnings dashboard
- Payout request system
- Store performance metrics
- Customer reviews management

### 🔧 **For Administrators**
- Comprehensive admin dashboard
- User management (approve, ban, promote)
- Store approval and moderation
- Product approval system
- Transaction monitoring
- Commission management (10% default)
- Payout approval system
- Revenue analytics and reports
- System settings configuration

---

## 🛠 Technology Stack

### **Backend**
- **PHP 7.4+** with OOP principles
- **MySQL 5.7+** for database
- **PDO** for secure database operations
- **XAMPP** for local development

### **Frontend**
- **HTML5** - Semantic markup
- **CSS3** - Modern, responsive design with Flexbox/Grid
- **JavaScript (ES6+)** - Dynamic interactions
- **Font Awesome 6** - Icons
- **Google Fonts (Poppins)** - Typography

### **Payment Integration**
- **Razorpay** - Payment gateway for Indian market

### **Deployment**
- **AWS EC2** - Cloud hosting
- **Apache** - Web server
- **SSL/HTTPS** - Secure connections

---

## 📁 Project Structure

```
PRODIGI/
├── admin/                      # Admin panel
│   ├── dashboard.php          # Admin dashboard
│   ├── users.php              # User management
│   ├── stores.php             # Store management
│   ├── products.php           # Product moderation
│   ├── transactions.php       # Transaction logs
│   └── settings.php           # System settings
├── api/                       # API endpoints
│   ├── cart-add.php           # Add to cart
│   ├── cart-remove.php        # Remove from cart
│   ├── cart-count.php         # Get cart count
│   ├── store-approval.php     # Store approval API
│   ├── product-approval.php   # Product approval API
│   └── search.php             # Search products
├── assets/                    # Static assets
│   ├── css/
│   │   ├── style.css          # Main stylesheet
│   │   ├── auth.css           # Authentication styles
│   │   └── admin.css          # Admin panel styles
│   ├── js/
│   │   ├── main.js            # Main JavaScript
│   │   └── admin.js           # Admin JavaScript
│   └── images/                # Image assets
├── classes/                   # PHP OOP Classes
│   ├── Database.php           # Database connection (Singleton)
│   ├── User.php               # User management
│   ├── Store.php              # Store operations
│   ├── Product.php            # Product management
│   ├── Cart.php               # Shopping cart
│   ├── Transaction.php        # Payment handling
│   ├── Admin.php              # Admin operations
│   └── Utils.php              # Helper functions
├── config/                    # Configuration
│   └── config.php             # Main config file
├── database/                  # Database files
│   └── prodigi_db.sql         # Database schema
├── seller/                    # Seller dashboard
│   ├── dashboard.php          # Seller dashboard
│   ├── create-store.php       # Store creation
│   ├── upload-product.php     # Product upload
│   ├── products.php           # Product management
│   └── analytics.php          # Sales analytics
├── uploads/                   # Upload directories
│   ├── files/                 # Product files (protected)
│   ├── images/                # Product images
│   ├── profiles/              # Profile pictures
│   └── stores/                # Store banners/logos
├── views/                     # View templates
│   ├── home.php               # Homepage template
│   └── includes/
│       ├── header.php         # Header component
│       └── footer.php         # Footer component
├── .htaccess                  # Apache configuration
├── index.php                  # Main entry point
├── login.php                  # Login page
├── register.php               # Registration page
├── logout.php                 # Logout handler
├── products.php               # Product listing
├── product.php                # Product details
├── cart.php                   # Shopping cart
├── checkout.php               # Checkout page
├── download.php               # Secure file download
└── README.md                  # This file
```

---

## 🚀 Installation

### **Prerequisites**
- XAMPP (PHP 7.4+, MySQL 5.7+, Apache)
- Web browser (Chrome, Firefox, Edge)
- Text editor (VS Code recommended)

### **Step 1: Download and Install XAMPP**
1. Download XAMPP from [https://www.apachefriends.org](https://www.apachefriends.org)
2. Install XAMPP to `C:\xampp\`
3. Start Apache and MySQL from XAMPP Control Panel

### **Step 2: Set Up Project**
1. The project is already in `C:\xampp\htdocs\PRODIGI\`
2. If not, clone or copy the project to this location

### **Step 3: Create Database**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "New" to create a database
3. Name it `prodigi_db`
4. Click "Import" tab
5. Choose file: `C:\xampp\htdocs\PRODIGI\database\prodigi_db.sql`
6. Click "Go" to import

### **Step 4: Configure Application**
1. Open `config/config.php`
2. Verify database settings:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'prodigi_db');
```

3. Update APP_URL if needed:
```php
define('APP_URL', 'http://localhost/PRODIGI');
```

### **Step 5: Set Permissions**
Ensure upload directories have write permissions:
- `uploads/files/`
- `uploads/images/`
- `uploads/profiles/`
- `uploads/stores/`

---

## ⚙️ Configuration

### **Payment Gateway Setup (Razorpay)**
1. Sign up at [https://razorpay.com](https://razorpay.com)
2. Get API keys from Dashboard → Settings → API Keys
3. Update in `config/config.php`:
```php
define('RAZORPAY_KEY_ID', 'rzp_test_xxxxx');
define('RAZORPAY_KEY_SECRET', 'xxxxxxxxxxxxx');
```

### **Admin Settings**
Access admin settings at: `http://localhost/PRODIGI/admin/settings.php`

Configure:
- Commission percentage (default: 10%)
- Maximum file upload size
- Download limits
- Email settings
- Maintenance mode

---

## 📖 Usage

### **Access the Application**
- **Homepage**: `http://localhost/PRODIGI`
- **Login**: `http://localhost/PRODIGI/login.php`
- **Register**: `http://localhost/PRODIGI/register.php`
- **Admin Panel**: `http://localhost/PRODIGI/admin/dashboard.php`

### **Default Admin Credentials**
- **Username**: `admin`
- **Password**: `admin123`

⚠️ **IMPORTANT**: Change admin password immediately after first login!

### **Creating a Seller Account**
1. Register as a new user
2. Login to your account
3. Navigate to "Become a Seller" or create a store
4. Wait for admin approval (if required)
5. Start uploading products

### **Uploading Products**
1. Login as seller
2. Go to Seller Dashboard
3. Click "Upload Product"
4. Fill in product details
5. Upload files (max 100MB)
6. Submit for approval
7. Once approved, product goes live

### **Buying Products**
1. Browse products
2. Add to cart
3. Proceed to checkout
4. Complete payment via Razorpay
5. Download product instantly

---

## ☁️ AWS Deployment

### **Step 1: Launch EC2 Instance**
1. Login to AWS Console
2. Navigate to EC2 → Launch Instance
3. Choose **Amazon Linux 2** or **Ubuntu 20.04**
4. Instance Type: **t2.micro** (Free Tier)
5. Configure Security Group:
   - HTTP (80)
   - HTTPS (443)
   - SSH (22)
6. Launch and download `.pem` key

### **Step 2: Connect to EC2**
```bash
ssh -i "your-key.pem" ec2-user@your-ec2-ip
```

### **Step 3: Install LAMP Stack**
```bash
# Update system
sudo yum update -y

# Install Apache
sudo yum install httpd -y
sudo systemctl start httpd
sudo systemctl enable httpd

# Install PHP 7.4
sudo amazon-linux-extras install php7.4 -y
sudo yum install php php-mysqlnd php-gd php-mbstring php-xml -y

# Install MySQL
sudo yum install mysql-server -y
sudo systemctl start mysqld
sudo systemctl enable mysqld
sudo mysql_secure_installation
```

### **Step 4: Deploy Application**
```bash
# Upload files via SCP
scp -i "your-key.pem" -r /path/to/PRODIGI ec2-user@your-ec2-ip:/home/ec2-user/

# Move to web directory
sudo mv /home/ec2-user/PRODIGI /var/www/html/

# Set permissions
sudo chown -R apache:apache /var/www/html/PRODIGI
sudo chmod -R 755 /var/www/html/PRODIGI
sudo chmod -R 777 /var/www/html/PRODIGI/uploads
```

### **Step 5: Import Database**
```bash
mysql -u root -p
CREATE DATABASE prodigi_db;
exit;

mysql -u root -p prodigi_db < /var/www/html/PRODIGI/database/prodigi_db.sql
```

### **Step 6: Configure SSL (Let's Encrypt)**
```bash
sudo yum install certbot python3-certbot-apache -y
sudo certbot --apache -d yourdomain.com
```

### **Step 7: Update Config**
Edit `/var/www/html/PRODIGI/config/config.php`:
```php
define('APP_URL', 'https://yourdomain.com/PRODIGI');
define('APP_ENV', 'production');
```

---

## 🔒 Security Features

### **Implemented Security**
- ✅ **CSRF Protection** - Token-based form validation
- ✅ **SQL Injection Prevention** - PDO prepared statements
- ✅ **XSS Protection** - Input sanitization and output escaping
- ✅ **Password Hashing** - bcrypt with salt
- ✅ **Session Security** - HTTPOnly cookies, timeout
- ✅ **File Upload Validation** - MIME type checking, size limits
- ✅ **Secure File Download** - Token-based access control
- ✅ **Directory Protection** - .htaccess configuration
- ✅ **HTTPS Enforcement** - SSL/TLS encryption

### **Best Practices**
- Change default admin password
- Use strong passwords (min 6 characters)
- Enable HTTPS in production
- Regular security updates
- Monitor access logs
- Backup database regularly

---

## 📡 API Documentation

### **Cart API**

#### Add to Cart
```
POST /api/cart-add.php
Content-Type: application/json

{
  "product_id": 123
}

Response:
{
  "success": true,
  "message": "Added to cart"
}
```

#### Get Cart Count
```
GET /api/cart-count.php

Response:
{
  "count": 3
}
```

### **Admin API**

#### Approve Store
```
POST /api/store-approval.php
Content-Type: application/json

{
  "store_id": 5,
  "action": "approve"
}
```

#### Approve Product
```
POST /api/product-approval.php
Content-Type: application/json

{
  "product_id": 42
}
```

---

## 🎨 Design System

### **Color Palette**
- **Primary**: `#4B6EF5` (Royal Blue)
- **Secondary**: `#00C2A8` (Aqua)
- **Accent**: `#F5B400` (Gold)
- **Background**: `#F8FAFC` (Light Grey)
- **Text**: `#1E293B` (Dark)

### **Typography**
- **Font Family**: Poppins (Google Fonts)
- **Weights**: 300, 400, 500, 600, 700

### **Responsive Breakpoints**
- Mobile: `< 480px`
- Tablet: `481px - 768px`
- Desktop: `> 769px`

---

## 📊 Database Schema

### **Key Tables**
- `users` - User accounts (buyers, sellers, admin)
- `stores` - Seller stores
- `products` - Digital products
- `categories` - Product categories
- `transactions` - Payment records
- `shopping_cart` - Cart items
- `reviews` - Product reviews
- `payouts` - Seller payout requests
- `admin_settings` - System configuration
- `activity_logs` - User activity tracking

---

## 🧪 Testing

### **Test Accounts**
```
Admin:
Username: admin
Password: admin123

(Create additional test users via registration)
```

### **Test Payment**
Use Razorpay test mode credentials for testing payments without real money.

---

## 🔄 Future Enhancements

- [ ] Email notifications system
- [ ] Advanced analytics dashboard
- [ ] Product bundling feature
- [ ] Affiliate marketing system
- [ ] Multi-currency support
- [ ] Social media integration
- [ ] Live chat support
- [ ] Mobile app (iOS/Android)
- [ ] Advanced SEO optimization
- [ ] Bulk product upload
- [ ] API for third-party integrations

---

## 🐛 Troubleshooting

### **Common Issues**

#### Database Connection Failed
```
Solution: Check MySQL is running, verify credentials in config.php
```

#### File Upload Errors
```
Solution: Check folder permissions (chmod 777 uploads/*)
```

#### Blank Page / White Screen
```
Solution: Check PHP error logs in xampp/apache/logs/error.log
```

#### Payment Gateway Errors
```
Solution: Verify Razorpay API keys in config.php
```

---

## 📞 Support

For issues, questions, or contributions:
- **Email**: support@prodigi.com
- **Documentation**: Check this README
- **GitHub Issues**: [Create an issue](#)

---

## 👨‍💻 Developer

**Project**: PRODIGI - Digital Marketplace  
**Architecture**: OOP PHP with MVC-inspired structure  
**Version**: 1.0.0  
**Date**: October 19, 2025  

---

## 📄 License

This project is developed for educational and commercial purposes.  
© 2025 PRODIGI. All rights reserved.

---

## 🙏 Acknowledgments

- **Font Awesome** - Icons
- **Google Fonts** - Typography
- **Razorpay** - Payment processing
- **XAMPP** - Development environment
- **AWS** - Cloud hosting

---

## ⭐ Features Checklist

- [x] User Authentication (Login/Register/Logout)
- [x] Role-based Access Control (Buyer/Seller/Admin)
- [x] Store Management
- [x] Product Upload & Management
- [x] Shopping Cart
- [x] Payment Integration (Razorpay)
- [x] Secure File Download
- [x] Admin Dashboard
- [x] Analytics & Reporting
- [x] Commission System
- [x] Payout Management
- [x] Product Reviews & Ratings
- [x] Search & Filtering
- [x] Responsive Design
- [x] Security Features
- [x] OOP Architecture
- [x] API Endpoints

---

**Happy Selling! 🎉**

Visit: `http://localhost/PRODIGI`
#   P r o d i g i  
 