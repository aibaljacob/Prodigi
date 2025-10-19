<?php
/**
 * PRODIGI - Digital Marketplace Configuration
 * Main configuration file for the application
 * Created: October 19, 2025
 */

// Prevent direct access
defined('PRODIGI_ACCESS') or define('PRODIGI_ACCESS', true);

// Error Reporting (Set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Timezone
date_default_timezone_set('Asia/Kolkata');

// Session Configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 0); // Set to 1 in production with HTTPS

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'aibaljacob33#');
define('DB_NAME', 'prodigi_db');
define('DB_CHARSET', 'utf8mb4');

// Application Configuration
define('APP_NAME', 'PRODIGI');
define('APP_URL', 'http://localhost/PRODIGI');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development, production

// Directory Paths
define('ROOT_PATH', dirname(__DIR__));
define('CLASSES_PATH', ROOT_PATH . '/classes');
define('INCLUDES_PATH', ROOT_PATH . '/includes');
define('UPLOADS_PATH', ROOT_PATH . '/uploads');
define('UPLOAD_DIR', ROOT_PATH . '/uploads'); // Alias for compatibility
define('ASSETS_PATH', ROOT_PATH . '/assets');
define('VIEWS_PATH', ROOT_PATH . '/views');

// Upload Directories (Outside public access)
define('PRODUCTS_UPLOAD_PATH', UPLOADS_PATH . '/products');
define('FILES_UPLOAD_PATH', UPLOADS_PATH . '/files');
define('IMAGES_UPLOAD_PATH', UPLOADS_PATH . '/images');
define('PROFILES_UPLOAD_PATH', UPLOADS_PATH . '/profiles');
define('STORES_UPLOAD_PATH', UPLOADS_PATH . '/stores');

// Public Assets URLs
define('ASSETS_URL', APP_URL . '/assets');
define('CSS_URL', ASSETS_URL . '/css');
define('JS_URL', ASSETS_URL . '/js');
define('IMG_URL', ASSETS_URL . '/images');

// File Upload Settings
define('MAX_FILE_SIZE', 100 * 1024 * 1024); // 100 MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_PRODUCT_TYPES', [
    'application/pdf',
    'image/jpeg', 'image/png', 'image/gif', 'image/svg+xml',
    'audio/mpeg', 'audio/wav', 'audio/ogg',
    'video/mp4', 'video/mpeg', 'video/quicktime',
    'application/zip', 'application/x-zip-compressed',
    'application/x-rar-compressed',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation'
]);

// Pagination
define('PRODUCTS_PER_PAGE', 12);
define('ADMIN_ITEMS_PER_PAGE', 20);

// Security Settings
define('PASSWORD_MIN_LENGTH', 6);
define('SESSION_TIMEOUT', 3600 * 2); // 2 hours
define('CSRF_TOKEN_EXPIRE', 3600); // 1 hour
define('DOWNLOAD_TOKEN_EXPIRE', 24); // 24 hours

// Email Configuration (for future implementation)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');
define('SMTP_FROM_EMAIL', 'noreply@prodigi.com');
define('SMTP_FROM_NAME', 'PRODIGI Marketplace');

// Payment Gateway - Razorpay (Test Mode)
define('RAZORPAY_KEY_ID', 'rzp_test_xxxxxxxxxx');
define('RAZORPAY_KEY_SECRET', 'xxxxxxxxxxxxxxxxxxxxxx');
define('RAZORPAY_CURRENCY', 'INR');

// Commission Settings (Can be overridden by admin settings)
define('DEFAULT_COMMISSION_PERCENTAGE', 10.00);
define('DEFAULT_DOWNLOAD_LIMIT', 3);
define('DEFAULT_DOWNLOAD_EXPIRY_HOURS', 24);

// Default Admin Credentials
define('DEFAULT_ADMIN_USERNAME', 'admin');
define('DEFAULT_ADMIN_PASSWORD', 'admin123'); // Change in production

// Application Features
define('ENABLE_REGISTRATION', true);
define('REQUIRE_EMAIL_VERIFICATION', true);
// In single-vendor mode, there are no external sellers; keep this false to avoid mixed flows
define('REQUIRE_SELLER_APPROVAL', false);
define('REQUIRE_PRODUCT_APPROVAL', true);
define('ENABLE_REVIEWS', true);
define('ENABLE_WISHLIST', true);

// Single-vendor mode: Admin is the only uploader/seller
define('SINGLE_VENDOR', true);
// Admin user id (store owner)
define('STORE_OWNER_ID', 1);

// Color Scheme (for email templates and future use)
define('PRIMARY_COLOR', '#4B6EF5');
define('SECONDARY_COLOR', '#00C2A8');
define('ACCENT_COLOR', '#F5B400');
define('BACKGROUND_COLOR', '#F8FAFC');

// Social Media (Optional)
define('FACEBOOK_URL', '');
define('TWITTER_URL', '');
define('INSTAGRAM_URL', '');
define('LINKEDIN_URL', '');

// Auto-create necessary directories
$directories = [
    UPLOADS_PATH,
    PRODUCTS_UPLOAD_PATH,
    FILES_UPLOAD_PATH,
    IMAGES_UPLOAD_PATH,
    PROFILES_UPLOAD_PATH,
    STORES_UPLOAD_PATH
];

foreach ($directories as $dir) {
    if (!file_exists($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Autoloader for Classes
spl_autoload_register(function ($class_name) {
    // Special handling for classes in Utils.php
    $utilsClasses = ['FileUpload', 'Utils', 'Category', 'Review'];
    if (in_array($class_name, $utilsClasses)) {
        $file = CLASSES_PATH . '/Utils.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
    
    // Standard autoloading
    $file = CLASSES_PATH . '/' . $class_name . '.php';
    if (file_exists($file)) {
        require_once $file;
    }
});

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize session timeout
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > SESSION_TIMEOUT)) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['LAST_ACTIVITY'] = time();

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

// Regenerate CSRF token if expired
if (isset($_SESSION['csrf_token_time']) && (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_EXPIRE)) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    $_SESSION['csrf_token_time'] = time();
}

// Development convenience: ensure default admin exists
if (APP_ENV === 'development') {
    try {
        $db = Database::getInstance();
        
        // Ensure default admin user exists
        $adminExists = $db->exists('users', 'username = :u', ['u' => DEFAULT_ADMIN_USERNAME]);
        $adminUserId = null;
        
        if (!$adminExists) {
            $adminUserId = $db->insert('users', [
                'username' => DEFAULT_ADMIN_USERNAME,
                'email' => 'admin@example.com',
                'password_hash' => password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT),
                'full_name' => 'Administrator',
                'user_type' => 'admin',
                'email_verified' => 1,
                'is_verified' => 1,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            // Ensure default admin can log in with configured password locally
            $db->update('users', [
                'user_type' => 'admin',
                'password_hash' => password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT),
                'email_verified' => 1,
                'is_verified' => 1,
                'is_active' => 1
            ], 'username = :u', ['u' => DEFAULT_ADMIN_USERNAME]);
            
            // Get admin user ID
            $adminUser = $db->fetchOne('SELECT user_id FROM users WHERE username = :u', ['u' => DEFAULT_ADMIN_USERNAME]);
            $adminUserId = $adminUser['user_id'] ?? 1;
        }
        
        // Ensure default store exists for single-vendor mode
        if (SINGLE_VENDOR) {
            $storeExists = $db->exists('stores', 'store_id = :id', ['id' => STORE_OWNER_ID]);
            if (!$storeExists) {
                // Check if there's any store for the admin user
                $existingStore = $db->fetchOne('SELECT store_id FROM stores WHERE user_id = :uid LIMIT 1', ['uid' => $adminUserId]);
                
                if ($existingStore) {
                    // Update existing store to use STORE_OWNER_ID
                    $db->query('UPDATE stores SET store_id = :new_id WHERE store_id = :old_id', [
                        'new_id' => STORE_OWNER_ID,
                        'old_id' => $existingStore['store_id']
                    ]);
                } else {
                    // Create default store
                    $db->query('INSERT INTO stores (store_id, user_id, store_name, store_slug, store_description, is_approved, is_active, created_at) 
                                VALUES (:store_id, :user_id, :name, :slug, :desc, 1, 1, NOW())', [
                        'store_id' => STORE_OWNER_ID,
                        'user_id' => $adminUserId,
                        'name' => APP_NAME . ' Store',
                        'slug' => strtolower(str_replace(' ', '-', APP_NAME)) . '-store',
                        'desc' => 'Official ' . APP_NAME . ' digital marketplace store'
                    ]);
                }
            }
        }
        
    } catch (Throwable $e) {
        // do not block app if seeding fails
    }
}

?>
