<?php
/**
 * Cart API - AJAX endpoint for cart operations
 */

define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!User::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$userId = User::getCurrentUserId();
$cart = new Cart();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'add':
        $productId = $input['product_id'] ?? 0;
        $result = $cart->addItem($userId, $productId);
        echo json_encode($result);
        break;
        
    case 'remove':
        $cartId = $input['cart_id'] ?? 0;
        $result = $cart->removeItem($cartId, $userId);
        echo json_encode($result);
        break;
        
    case 'get':
        $items = $cart->getCartItems($userId);
        $total = $cart->getCartTotal($userId);
        $count = $cart->getCartCount($userId);
        echo json_encode([
            'success' => true,
            'items' => $items,
            'total' => $total,
            'count' => $count
        ]);
        break;
        
    case 'clear':
        $cart->clearCart($userId);
        echo json_encode(['success' => true, 'message' => 'Cart cleared']);
        break;
        
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
