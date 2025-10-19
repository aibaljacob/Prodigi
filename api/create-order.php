<?php
/**
 * Create Razorpay Order API
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!User::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// CSRF validation
if (!isset($input['csrf_token']) || $input['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$userId = User::getCurrentUserId();
$cart = new Cart();
$cartItems = $cart->getCartItems($userId);

if (empty($cartItems)) {
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    exit;
}

$cartTotal = $cart->getCartTotal($userId);
$amountInPaise = $cartTotal * 100; // Convert to paise

try {
    // Create Razorpay order
    $ch = curl_init('https://api.razorpay.com/v1/orders');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'amount' => $amountInPaise,
        'currency' => RAZORPAY_CURRENCY,
        'receipt' => 'order_' . time() . '_' . $userId,
        'notes' => [
            'user_id' => $userId,
            'items_count' => count($cartItems)
        ]
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Razorpay API Error: HTTP $httpCode - Response: $response - cURL Error: $curlError");
        throw new Exception('Failed to create Razorpay order. HTTP Code: ' . $httpCode);
    }
    
    $orderData = json_decode($response, true);
    
    if (!$orderData || !isset($orderData['id'])) {
        error_log("Razorpay Response Parse Error: " . $response);
        throw new Exception('Invalid response from payment gateway');
    }
    
    // Store order details in database
    $db = Database::getInstance();
    
    // Get store owner user_id (in single-vendor mode, it's the admin)
    $storeOwner = $db->fetchOne("SELECT user_id FROM stores WHERE store_id = :store_id", [
        'store_id' => STORE_OWNER_ID
    ]);
    $sellerId = $storeOwner ? $storeOwner['user_id'] : STORE_OWNER_ID;
    
    // Generate unique transaction UUID
    $transactionUuid = uniqid('TXN_', true);
    
    foreach ($cartItems as $item) {
        $amount = $item['discount_price'] ?: $item['price'];
        $commissionPercentage = DEFAULT_COMMISSION_PERCENTAGE;
        $commissionAmount = $amount * ($commissionPercentage / 100);
        $sellerEarnings = $amount - $commissionAmount;
        
        $db->insert('transactions', [
            'transaction_uuid' => $transactionUuid . '_' . $item['product_id'],
            'buyer_id' => $userId,
            'seller_id' => $sellerId,
            'product_id' => $item['product_id'],
            'amount' => $amount,
            'total_amount' => $amount,
            'commission_percentage' => $commissionPercentage,
            'commission_amount' => $commissionAmount,
            'seller_earnings' => $sellerEarnings,
            'payment_gateway' => 'razorpay',
            'razorpay_order_id' => $orderData['id'],
            'payment_status' => 'pending'
        ]);
    }
    
    echo json_encode([
        'success' => true,
        'order_id' => $orderData['id'],
        'amount' => $orderData['amount'],
        'currency' => $orderData['currency']
    ]);
    
} catch (Exception $e) {
    error_log('Razorpay Order Creation Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create order. Please try again.'
    ]);
}
