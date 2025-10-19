<?php
/**
 * API: Product Approval
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!User::isAdmin()) {
    Utils::jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;

if (!$productId) {
    Utils::jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
}

$product = new Product();
$result = $product->approveProduct($productId);

Utils::jsonResponse($result);
?>
