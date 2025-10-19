<?php
/**
 * Migration: Add allowed_file_types column to categories table
 * This allows each category to specify which file types are allowed for products
 */

define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../../config/config.php';

$db = Database::getInstance();

try {
    echo "Starting migration: Add allowed_file_types to categories...\n";
    
    // Add allowed_file_types column (JSON field to store array of allowed MIME types)
    echo "Adding allowed_file_types column... ";
    
    // Check if column exists first
    $checkColumn = $db->fetchOne("SHOW COLUMNS FROM categories LIKE 'allowed_file_types'");
    
    if (!$checkColumn) {
        $db->query("ALTER TABLE categories ADD COLUMN allowed_file_types JSON DEFAULT NULL");
        echo "✓ Added allowed_file_types column\n";
    } else {
        echo "✓ Column already exists\n";
    }
    
    // Set default allowed file types for existing categories based on common patterns
    echo "Setting default file types for existing categories... ";
    
    // Default file types for common categories
    $defaultMappings = [
        'audio' => json_encode(['audio/mpeg', 'audio/wav', 'audio/ogg', 'audio/mp3']),
        'video' => json_encode(['video/mp4', 'video/mpeg', 'video/quicktime', 'video/x-msvideo']),
        'ebook' => json_encode(['application/pdf', 'application/epub+zip']),
        'document' => json_encode(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain']),
        'image' => json_encode(['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml']),
        'software' => json_encode(['application/zip', 'application/x-zip-compressed', 'application/x-rar-compressed']),
    ];
    
    foreach ($defaultMappings as $keyword => $fileTypes) {
        $db->query(
            "UPDATE categories SET allowed_file_types = :file_types 
             WHERE LOWER(category_name) LIKE :keyword AND allowed_file_types IS NULL",
            ['file_types' => $fileTypes, 'keyword' => "%$keyword%"]
        );
    }
    
    echo "✓ Set default file types\n";
    
    echo "\n✅ Migration completed successfully!\n";
    echo "Note: Categories without specific file types will allow all file types.\n";
    echo "You can now set specific file types for each category in the admin panel.\n";
    
} catch (Exception $e) {
    echo "\n❌ Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
?>
