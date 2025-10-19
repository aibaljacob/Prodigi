<?php
/**
 * Verify Razorpay Payment API
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

$razorpayOrderId = $input['razorpay_order_id'] ?? '';
$razorpayPaymentId = $input['razorpay_payment_id'] ?? '';
$razorpaySignature = $input['razorpay_signature'] ?? '';

if (empty($razorpayOrderId) || empty($razorpayPaymentId) || empty($razorpaySignature)) {
    echo json_encode(['success' => false, 'message' => 'Invalid payment response']);
    exit;
}

try {
    // Verify signature
    $generatedSignature = hash_hmac('sha256', $razorpayOrderId . '|' . $razorpayPaymentId, RAZORPAY_KEY_SECRET);
    
    if ($generatedSignature !== $razorpaySignature) {
        throw new Exception('Payment signature verification failed');
    }
    
    $userId = User::getCurrentUserId();
    $db = Database::getInstance();
    
    // Update transactions
    $db->query(
        "UPDATE transactions 
         SET payment_status = 'completed',
             razorpay_payment_id = :payment_id,
             razorpay_signature = :signature,
             paid_at = NOW()
         WHERE razorpay_order_id = :order_id 
         AND buyer_id = :user_id",
        [
            'payment_id' => $razorpayPaymentId,
            'signature' => $razorpaySignature,
            'order_id' => $razorpayOrderId,
            'user_id' => $userId
        ]
    );
    
    // Update product sales count
    $transactions = $db->fetchAll(
        "SELECT product_id FROM transactions 
         WHERE razorpay_order_id = :order_id AND buyer_id = :user_id",
        ['order_id' => $razorpayOrderId, 'user_id' => $userId]
    );
    
    foreach ($transactions as $transaction) {
        $db->query(
            "UPDATE products 
             SET total_sales = total_sales + 1 
             WHERE product_id = :product_id",
            ['product_id' => $transaction['product_id']]
        );
    }
    
    // Clear user's cart
    $cart = new Cart();
    $cart->clearCart($userId);
    
    echo json_encode([
        'success' => true,
        'message' => 'Payment verified successfully'
    ]);
    
} catch (Exception $e) {
    error_log('Payment Verification Error: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Payment verification failed'
    ]);
}
