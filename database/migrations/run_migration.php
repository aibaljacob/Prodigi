<?php
/**
 * Database Migration Script
 * Run this file to add missing product columns
 */

define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../../config/config.php';

echo "Starting database migration...\n\n";

try {
    $db = Database::getInstance();
    
    // Check if columns already exist
    $checkQuery = "SHOW COLUMNS FROM products LIKE 'short_description'";
    $exists = $db->fetchOne($checkQuery);
    
    if ($exists) {
        echo "✓ Columns already exist. Migration already run.\n";
        exit(0);
    }
    
    echo "Adding short_description column...\n";
    $db->query("ALTER TABLE products ADD COLUMN short_description VARCHAR(255) DEFAULT NULL AFTER product_description");
    echo "✓ short_description column added\n\n";
    
    echo "Adding product file columns...\n";
    $db->query("ALTER TABLE products ADD COLUMN product_file_path VARCHAR(255) DEFAULT NULL AFTER thumbnail_image");
    echo "✓ product_file_path column added\n";
    
    $db->query("ALTER TABLE products ADD COLUMN product_file_original_name VARCHAR(255) DEFAULT NULL AFTER product_file_path");
    echo "✓ product_file_original_name column added\n";
    
    $db->query("ALTER TABLE products ADD COLUMN product_file_size_bytes BIGINT DEFAULT NULL AFTER product_file_original_name");
    echo "✓ product_file_size_bytes column added\n\n";
    
    echo "Adding index...\n";
    $db->query("ALTER TABLE products ADD INDEX idx_product_file (product_file_path)");
    echo "✓ Index added\n\n";
    
    echo "Updating file_size_mb for existing records...\n";
    $db->query("UPDATE products SET file_size_mb = ROUND(product_file_size_bytes / 1048576, 2) WHERE product_file_size_bytes IS NOT NULL");
    echo "✓ file_size_mb updated\n\n";
    
    echo "✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
