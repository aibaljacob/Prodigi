<?php
/**
 * PRODIGI - System Check
 * Verifies installation and configuration
 */

define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRODIGI System Check</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
            min-height: 100vh;
        }
        .container { 
            max-width: 800px; 
            margin: 0 auto; 
            background: white; 
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        h1 { 
            color: #333; 
            margin-bottom: 10px;
            font-size: 28px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .check-item { 
            padding: 15px; 
            margin: 10px 0; 
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .check-item.success { 
            background: #d4edda; 
            border-left: 4px solid #28a745;
        }
        .check-item.error { 
            background: #f8d7da; 
            border-left: 4px solid #dc3545;
        }
        .check-item.warning { 
            background: #fff3cd; 
            border-left: 4px solid #ffc107;
        }
        .status { 
            font-weight: bold;
            font-size: 18px;
        }
        .success .status { color: #28a745; }
        .error .status { color: #dc3545; }
        .warning .status { color: #ffc107; }
        .details { 
            font-size: 12px; 
            color: #666; 
            margin-top: 5px;
        }
        .section { 
            margin: 30px 0;
            padding-top: 20px;
            border-top: 2px solid #eee;
        }
        .section-title {
            font-size: 20px;
            color: #444;
            margin-bottom: 15px;
            font-weight: 600;
        }
        .summary {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-top: 30px;
        }
        .summary h3 {
            color: #333;
            margin-bottom: 10px;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 PRODIGI System Check</h1>
        <p class="subtitle">Installation verification and diagnostics</p>

        <?php
        $errors = 0;
        $warnings = 0;
        $success = 0;

        // Check 1: PHP Version
        echo '<div class="section">';
        echo '<h2 class="section-title">PHP Environment</h2>';
        
        $phpVersion = phpversion();
        if (version_compare($phpVersion, '7.4.0', '>=')) {
            echo '<div class="check-item success">';
            echo '<div><strong>PHP Version:</strong> ' . $phpVersion . '<div class="details">Minimum required: PHP 7.4</div></div>';
            echo '<span class="status">✓</span>';
            echo '</div>';
            $success++;
        } else {
            echo '<div class="check-item error">';
            echo '<div><strong>PHP Version:</strong> ' . $phpVersion . '<div class="details">Please upgrade to PHP 7.4 or higher</div></div>';
            echo '<span class="status">✗</span>';
            echo '</div>';
            $errors++;
        }

        // Check 2: Required Extensions
        $extensions = ['pdo', 'pdo_mysql', 'mysqli', 'gd', 'mbstring', 'json'];
        foreach ($extensions as $ext) {
            if (extension_loaded($ext)) {
                echo '<div class="check-item success">';
                echo '<div><strong>Extension:</strong> ' . $ext . '</div>';
                echo '<span class="status">✓</span>';
                echo '</div>';
                $success++;
            } else {
                echo '<div class="check-item error">';
                echo '<div><strong>Extension:</strong> ' . $ext . '<div class="details">Please enable this extension in php.ini</div></div>';
                echo '<span class="status">✗</span>';
                echo '</div>';
                $errors++;
            }
        }

        // Check 3: Database Connection
        echo '</div>';
        echo '<div class="section">';
        echo '<h2 class="section-title">Database Connection</h2>';
        
        try {
            $db = Database::getInstance();
            echo '<div class="check-item success">';
            echo '<div><strong>Database Connection</strong><div class="details">Successfully connected to ' . DB_NAME . '</div></div>';
            echo '<span class="status">✓</span>';
            echo '</div>';
            $success++;

            // Check tables
            $tables = ['users', 'categories', 'stores', 'products', 'transactions', 'shopping_cart'];
            foreach ($tables as $table) {
                if ($db->exists($table)) {
                    echo '<div class="check-item success">';
                    echo '<div><strong>Table:</strong> ' . $table . '</div>';
                    echo '<span class="status">✓</span>';
                    echo '</div>';
                    $success++;
                } else {
                    echo '<div class="check-item error">';
                    echo '<div><strong>Table:</strong> ' . $table . '<div class="details">Table not found. Please import database.</div></div>';
                    echo '<span class="status">✗</span>';
                    echo '</div>';
                    $errors++;
                }
            }
        } catch (Exception $e) {
            echo '<div class="check-item error">';
            echo '<div><strong>Database Connection</strong><div class="details">' . htmlspecialchars($e->getMessage()) . '</div></div>';
            echo '<span class="status">✗</span>';
            echo '</div>';
            $errors++;
        }

        // Check 4: File System
        echo '</div>';
        echo '<div class="section">';
        echo '<h2 class="section-title">File System</h2>';

        $directories = [
            'uploads' => UPLOADS_PATH,
            'uploads/products' => PRODUCTS_UPLOAD_PATH,
            'uploads/files' => FILES_UPLOAD_PATH,
            'uploads/stores' => STORES_UPLOAD_PATH,
            'uploads/users' => PROFILES_UPLOAD_PATH
        ];

        foreach ($directories as $name => $path) {
            if (file_exists($path) && is_writable($path)) {
                echo '<div class="check-item success">';
                echo '<div><strong>Directory:</strong> ' . $name . '<div class="details">Writable</div></div>';
                echo '<span class="status">✓</span>';
                echo '</div>';
                $success++;
            } else if (file_exists($path)) {
                echo '<div class="check-item warning">';
                echo '<div><strong>Directory:</strong> ' . $name . '<div class="details">Not writable - file uploads may fail</div></div>';
                echo '<span class="status">⚠</span>';
                echo '</div>';
                $warnings++;
            } else {
                echo '<div class="check-item error">';
                echo '<div><strong>Directory:</strong> ' . $name . '<div class="details">Does not exist</div></div>';
                echo '<span class="status">✗</span>';
                echo '</div>';
                $errors++;
            }
        }

        // Check 5: Classes
        echo '</div>';
        echo '<div class="section">';
        echo '<h2 class="section-title">Core Classes</h2>';

        $classes = ['Database', 'User', 'Product', 'Store', 'Cart', 'Transaction', 'Admin', 'Category', 'Utils'];
        foreach ($classes as $className) {
            if (class_exists($className)) {
                echo '<div class="check-item success">';
                echo '<div><strong>Class:</strong> ' . $className . '</div>';
                echo '<span class="status">✓</span>';
                echo '</div>';
                $success++;
            } else {
                echo '<div class="check-item error">';
                echo '<div><strong>Class:</strong> ' . $className . '<div class="details">Class not found or not loading</div></div>';
                echo '<span class="status">✗</span>';
                echo '</div>';
                $errors++;
            }
        }

        echo '</div>';

        // Summary
        $total = $success + $errors + $warnings;
        echo '<div class="summary">';
        echo '<h3>📊 Summary</h3>';
        echo '<p><strong>Total Checks:</strong> ' . $total . '</p>';
        echo '<p style="color: #28a745;"><strong>Passed:</strong> ' . $success . '</p>';
        if ($warnings > 0) {
            echo '<p style="color: #ffc107;"><strong>Warnings:</strong> ' . $warnings . '</p>';
        }
        if ($errors > 0) {
            echo '<p style="color: #dc3545;"><strong>Errors:</strong> ' . $errors . '</p>';
        }

        if ($errors === 0) {
            echo '<p style="margin-top: 20px; color: #28a745; font-weight: bold;">✅ Your PRODIGI installation is working correctly!</p>';
            echo '<a href="index.php" class="btn">Go to Homepage</a>';
            echo ' <a href="login.php" class="btn">Admin Login</a>';
        } else {
            echo '<p style="margin-top: 20px; color: #dc3545; font-weight: bold;">❌ Please fix the errors above before proceeding.</p>';
            echo '<p style="margin-top: 10px;">Check <strong>TROUBLESHOOTING.md</strong> for solutions.</p>';
        }
        echo '</div>';
        ?>

    </div>
</body>
</html>
