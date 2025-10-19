<?php
/**
 * API: Add to Cart
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    Utils::jsonResponse(['success' => false, 'message' => 'Please login first'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$productId = $input['product_id'] ?? null;

if (!$productId) {
    Utils::jsonResponse(['success' => false, 'message' => 'Product ID required'], 400);
}

$cart = new Cart();
$result = $cart->addItem(User::getCurrentUserId(), $productId);

Utils::jsonResponse($result, $result['success'] ? 200 : 400);
?>
