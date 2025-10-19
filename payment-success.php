<?php
/**
 * Payment Success Page
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Redirect if not logged in
if (!User::isLoggedIn()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$orderId = $_GET['order_id'] ?? '';
if (empty($orderId)) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$userId = User::getCurrentUserId();
$db = Database::getInstance();

// Get order details
$purchases = $db->fetchAll(
    "SELECT t.*, p.product_name, p.product_slug, p.thumbnail_image, p.product_file_path
     FROM transactions t
     JOIN products p ON t.product_id = p.product_id
     WHERE t.razorpay_order_id = :order_id 
     AND t.buyer_id = :user_id
     AND t.payment_status = 'completed'",
    ['order_id' => $orderId, 'user_id' => $userId]
);

if (empty($purchases)) {
    header('Location: ' . APP_URL . '/');
    exit;
}

$totalAmount = array_sum(array_column($purchases, 'total_amount'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .success-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            text-align: center;
        }
        
        .success-icon {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(57, 255, 20, 0.1);
            border: 3px solid var(--neon-green);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem;
            animation: successPulse 1.5s ease-in-out infinite;
        }
        
        .success-icon i {
            font-size: 3rem;
            color: var(--neon-green);
        }
        
        @keyframes successPulse {
            0%, 100% {
                box-shadow: 0 0 20px rgba(57, 255, 20, 0.3);
            }
            50% {
                box-shadow: 0 0 40px rgba(57, 255, 20, 0.6);
            }
        }
        
        .success-title {
            font-size: 2.5rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .success-message {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        
        .order-summary {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 2rem;
            margin: 2rem 0;
            text-align: left;
        }
        
        .order-summary h3 {
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .purchased-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
        }
        
        .purchased-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }
        
        .purchased-item-details h4 {
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .purchased-item-details p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .download-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            background: rgba(57, 255, 20, 0.05);
            border-radius: var(--radius-sm);
            margin-top: 1rem;
        }
        
        .total-label {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .total-value {
            color: var(--neon-green);
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }
    </style>
</head>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="success-container">
            <div class="success-icon">
                <i class="fas fa-check"></i>
            </div>
            
            <h1 class="success-title">Payment Successful!</h1>
            <p class="success-message">
                Thank you for your purchase. Your order has been confirmed and you can now download your products.
            </p>
            
            <div class="order-summary">
                <h3>Order Summary</h3>
                
                <?php foreach ($purchases as $purchase): ?>
                <div class="purchased-item">
                    <img src="<?php echo $purchase['thumbnail_image'] ? APP_URL . '/uploads/products/' . $purchase['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($purchase['product_name']); ?>"
                         class="purchased-item-image">
                    
                    <div class="purchased-item-details">
                        <h4><?php echo htmlspecialchars($purchase['product_name']); ?></h4>
                        <p>Order ID: <?php echo htmlspecialchars($purchase['razorpay_order_id']); ?></p>
                        <p>Amount: <?php echo Utils::formatCurrency($purchase['total_amount']); ?></p>
                    </div>
                    
                    <div>
                        <?php if ($purchase['product_file_path']): ?>
                        <a href="<?php echo APP_URL; ?>/download.php?transaction_id=<?php echo $purchase['transaction_id']; ?>" 
                           class="btn btn-primary download-btn">
                            <i class="fas fa-download"></i> Download
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
                
                <div class="total-row">
                    <span class="total-label">Total Paid:</span>
                    <span class="total-value"><?php echo Utils::formatCurrency($totalAmount); ?></span>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="<?php echo APP_URL; ?>/profile.php" class="btn btn-outline btn-lg">
                    <i class="fas fa-user"></i> View Profile
                </a>
                <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-secondary btn-lg">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        </div>
    </div>
    
    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
</body>
</html>
