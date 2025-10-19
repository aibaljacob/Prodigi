<?php
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../../config/config.php';

$db = Database::getInstance();
$products = $db->fetchAll('SELECT product_id, product_name, thumbnail_image, product_file_path FROM products LIMIT 10');

echo "=== Product File Paths ===\n\n";
foreach($products as $p) {
    echo "ID: " . $p['product_id'] . "\n";
    echo "Name: " . $p['product_name'] . "\n";
    echo "Thumbnail: " . ($p['thumbnail_image'] ?? 'NULL') . "\n";
    echo "File: " . ($p['product_file_path'] ?? 'NULL') . "\n";
    
    // Check if files exist
    if ($p['thumbnail_image']) {
        $thumbPath = __DIR__ . '/../../' . $p['thumbnail_image'];
        echo "Thumb exists: " . (file_exists($thumbPath) ? "YES" : "NO - $thumbPath") . "\n";
    }
    if ($p['product_file_path']) {
        $filePath = __DIR__ . '/../../' . $p['product_file_path'];
        echo "File exists: " . (file_exists($filePath) ? "YES" : "NO - $filePath") . "\n";
    }
    echo "\n";
}
?>
