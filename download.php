<?php
/**
 * Secure Download System
 * Handles product file downloads with access control and tracking
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Redirect if not logged in
if (!User::isLoggedIn()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$transactionId = $_GET['transaction_id'] ?? 0;
$userId = User::getCurrentUserId();

if (!$transactionId) {
    die('Invalid download link');
}

$db = Database::getInstance();

// Get transaction and product details
$transaction = $db->fetchOne(
    "SELECT t.*, p.product_name, p.product_file_path, p.product_file_original_name, 
            p.download_limit, p.download_expiry_hours
     FROM transactions t
     JOIN products p ON t.product_id = p.product_id
     WHERE t.transaction_id = :transaction_id 
     AND t.buyer_id = :user_id
     AND t.payment_status = 'completed'",
    ['transaction_id' => $transactionId, 'user_id' => $userId]
);

if (!$transaction) {
    die('Download not authorized or payment not completed');
}

// Check if file exists
if (empty($transaction['product_file_path'])) {
    die('File not available for this product');
}

// The product_file_path already contains 'uploads/files/filename', so just use base directory
$filePath = __DIR__ . '/' . $transaction['product_file_path'];

if (!file_exists($filePath)) {
    error_log("Download file not found: " . $filePath);
    error_log("Expected path from DB: " . $transaction['product_file_path']);
    die('File not found. Please contact support.');
}

// Check download limit
if ($transaction['download_limit'] > 0) {
    $currentDownloads = $db->fetchOne(
        "SELECT COUNT(*) as count FROM download_logs 
         WHERE transaction_id = :transaction_id",
        ['transaction_id' => $transactionId]
    );
    
    if ($currentDownloads['count'] >= $transaction['download_limit']) {
        die('Download limit exceeded. You have reached the maximum number of downloads for this product.');
    }
}

// Check download expiry
if ($transaction['download_expiry_hours'] > 0) {
    $expiryTime = strtotime($transaction['paid_at']) + ($transaction['download_expiry_hours'] * 3600);
    if (time() > $expiryTime) {
        die('Download link expired. Please contact support for assistance.');
    }
}

// Log download
$db->insert('download_logs', [
    'transaction_id' => $transactionId,
    'product_id' => $transaction['product_id'],
    'user_id' => $userId,
    'ip_address' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
]);

// Update download count
$db->query(
    "UPDATE products SET total_downloads = total_downloads + 1 
     WHERE product_id = :product_id",
    ['product_id' => $transaction['product_id']]
);

// Serve file
$fileName = $transaction['product_file_original_name'] ?: basename($transaction['product_file_path']);
$fileSize = filesize($filePath);
$mimeType = mime_content_type($filePath);

// Set headers for download
header('Content-Type: ' . $mimeType);
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . $fileSize);
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: public');
header('Expires: 0');

// Clear output buffer
if (ob_get_level()) {
    ob_end_clean();
}

// Read and output file in chunks
$chunkSize = 8192;
$handle = fopen($filePath, 'rb');

if ($handle === false) {
    die('Error opening file');
}

while (!feof($handle)) {
    $buffer = fread($handle, $chunkSize);
    echo $buffer;
    flush();
}

fclose($handle);
exit;
