<?php
/**
 * Migration: Fix product file paths
 * Updates paths that are missing the 'uploads/' prefix
 */

define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../../config/config.php';

$db = Database::getInstance();

try {
    echo "Starting migration: Fix product file paths...\n\n";
    
    // Fix thumbnail paths
    echo "Fixing thumbnail paths... ";
    $db->query("
        UPDATE products 
        SET thumbnail_image = CONCAT('uploads/products/', thumbnail_image)
        WHERE thumbnail_image IS NOT NULL 
        AND thumbnail_image != ''
        AND thumbnail_image NOT LIKE 'uploads/%'
    ");
    echo "✓ Fixed\n";
    
    // Fix product file paths
    echo "Fixing product file paths... ";
    $db->query("
        UPDATE products 
        SET product_file_path = CONCAT('uploads/files/', product_file_path)
        WHERE product_file_path IS NOT NULL 
        AND product_file_path != ''
        AND product_file_path NOT LIKE 'uploads/%'
    ");
    echo "✓ Fixed\n";
    
    echo "\n✅ Migration completed successfully!\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
