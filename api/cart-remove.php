<?php
/**
 * API: Remove from Cart
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
$cartId = $input['cart_id'] ?? null;

if (!$cartId) {
    Utils::jsonResponse(['success' => false, 'message' => 'Cart ID required'], 400);
}

$cart = new Cart();
$result = $cart->removeItem($cartId, User::getCurrentUserId());

Utils::jsonResponse($result, $result['success'] ? 200 : 400);
?>
