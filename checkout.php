<?php
/**
 * Checkout Page with Razorpay Integration
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Redirect if not logged in
if (!User::isLoggedIn()) {
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$userId = User::getCurrentUserId();
$cart = new Cart();
$user = new User($userId);

// Get cart items
$cartItems = $cart->getCartItems($userId);
if (empty($cartItems)) {
    header('Location: ' . APP_URL . '/cart.php');
    exit;
}

$cartTotal = $cart->getCartTotal($userId);
$userData = $user->getUserById($userId);

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 2rem;
            padding: 3rem 1.5rem;
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .checkout-section {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 2rem;
        }
        
        .checkout-section h2 {
            font-size: 1.5rem;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .order-item {
            display: grid;
            grid-template-columns: 80px 1fr auto;
            gap: 1rem;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
        }
        
        .order-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }
        
        .order-item-details h3 {
            font-size: 1rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .order-item-details p {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        .order-item-price {
            font-size: 1.2rem;
            font-weight: 700;
            color: var(--neon-green);
            text-align: right;
        }
        
        .order-summary {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .summary-row:last-of-type {
            border-bottom: 2px solid var(--neon-green);
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .summary-label {
            color: var(--text-secondary);
        }
        
        .summary-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .summary-total {
            color: var(--neon-green);
        }
        
        .user-info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 1rem;
            background: var(--bg-primary);
            border-radius: var(--radius-sm);
            margin-bottom: 0.75rem;
        }
        
        .user-info-label {
            color: var(--text-muted);
        }
        
        .user-info-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .payment-methods {
            display: grid;
            gap: 1rem;
            margin: 1.5rem 0;
        }
        
        .payment-method {
            padding: 1rem;
            border: 2px solid var(--border-color);
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .payment-method:hover {
            border-color: var(--neon-green);
            background: rgba(57, 255, 20, 0.05);
        }
        
        .payment-method.active {
            border-color: var(--neon-green);
            background: rgba(57, 255, 20, 0.1);
        }
        
        .payment-method i {
            font-size: 1.5rem;
            color: var(--neon-green);
        }
        
        .payment-method-info h4 {
            color: var(--text-primary);
            margin-bottom: 0.25rem;
        }
        
        .payment-method-info p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .checkout-container {
                grid-template-columns: 1fr;
            }
            
            .order-summary {
                position: relative;
                top: 0;
            }
        }
    </style>
</head>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="checkout-container">
            <!-- Order Details -->
            <div>
                <div class="checkout-section">
                    <h2><i class="fas fa-shopping-cart"></i> Order Details</h2>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="order-item">
                        <img src="<?php echo $item['thumbnail_image'] ? APP_URL . '/' . $item['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                             class="order-item-image">
                        
                        <div class="order-item-details">
                            <h3><?php echo htmlspecialchars($item['product_name']); ?></h3>
                            <p><i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['category_name']); ?></p>
                        </div>
                        
                        <div class="order-item-price">
                            <?php echo Utils::formatCurrency($item['discount_price'] ?: $item['price']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="checkout-section" style="margin-top: 2rem;">
                    <h2><i class="fas fa-user"></i> Billing Information</h2>
                    
                    <div class="user-info-row">
                        <span class="user-info-label">Name:</span>
                        <span class="user-info-value"><?php echo htmlspecialchars($userData['full_name'] ?? $userData['username']); ?></span>
                    </div>
                    
                    <div class="user-info-row">
                        <span class="user-info-label">Email:</span>
                        <span class="user-info-value"><?php echo htmlspecialchars($userData['email']); ?></span>
                    </div>
                    
                    <?php if (!empty($userData['phone'])): ?>
                    <div class="user-info-row">
                        <span class="user-info-label">Phone:</span>
                        <span class="user-info-value"><?php echo htmlspecialchars($userData['phone']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="checkout-section" style="margin-top: 2rem;">
                    <h2><i class="fas fa-credit-card"></i> Payment Method</h2>
                    
                    <div class="payment-methods">
                        <div class="payment-method active" id="razorpay-method">
                            <i class="fas fa-wallet"></i>
                            <div class="payment-method-info">
                                <h4>Razorpay</h4>
                                <p>Pay securely with Credit Card, Debit Card, UPI, Net Banking & more</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Order Summary -->
            <div class="checkout-section order-summary">
                <h2>Order Summary</h2>
                
                <div class="summary-row">
                    <span class="summary-label">Subtotal:</span>
                    <span class="summary-value"><?php echo Utils::formatCurrency($cartTotal); ?></span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Tax:</span>
                    <span class="summary-value">₹0.00</span>
                </div>
                
                <div class="summary-row">
                    <span class="summary-label">Total:</span>
                    <span class="summary-value summary-total"><?php echo Utils::formatCurrency($cartTotal); ?></span>
                </div>
                
                <button id="pay-button" class="btn btn-secondary btn-lg" style="width: 100%; margin-top: 1.5rem;">
                    <i class="fas fa-lock"></i> Pay Now
                </button>
                
                <div style="margin-top: 1rem; text-align: center;">
                    <small style="color: var(--text-muted);">
                        <i class="fas fa-shield-alt"></i> Secure Payment
                    </small>
                </div>
                
                <div style="margin-top: 1.5rem; padding: 1rem; background: rgba(57, 255, 20, 0.05); border: 1px solid var(--neon-green); border-radius: var(--radius-sm);">
                    <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0;">
                        <i class="fas fa-info-circle"></i> After successful payment, you'll get instant access to download your purchased products.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
        const payButton = document.getElementById('pay-button');
        const cartTotal = <?php echo $cartTotal * 100; ?>; // Convert to paise
        
        payButton.addEventListener('click', function() {
            payButton.disabled = true;
            payButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Create order on server
            fetch('<?php echo APP_URL; ?>/api/create-order.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    amount: cartTotal,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Failed to create order');
                }
                
                // Initialize Razorpay
                const options = {
                    key: '<?php echo RAZORPAY_KEY_ID; ?>',
                    amount: cartTotal,
                    currency: '<?php echo RAZORPAY_CURRENCY; ?>',
                    name: 'PRODIGI',
                    description: 'Digital Products Purchase',
                    order_id: data.order_id,
                    prefill: {
                        name: '<?php echo htmlspecialchars($userData['full_name'] ?? $userData['username']); ?>',
                        email: '<?php echo htmlspecialchars($userData['email']); ?>',
                        contact: '<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>'
                    },
                    theme: {
                        color: '#39FF14'
                    },
                    handler: function(response) {
                        // Payment successful, verify on server
                        verifyPayment(response, data.order_id);
                    },
                    modal: {
                        ondismiss: function() {
                            payButton.disabled = false;
                            payButton.innerHTML = '<i class="fas fa-lock"></i> Pay Now';
                        }
                    }
                };
                
                const razorpay = new Razorpay(options);
                razorpay.open();
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred. Please try again.');
                payButton.disabled = false;
                payButton.innerHTML = '<i class="fas fa-lock"></i> Pay Now';
            });
        });
        
        function verifyPayment(razorpayResponse, orderId) {
            fetch('<?php echo APP_URL; ?>/api/verify-payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    razorpay_order_id: razorpayResponse.razorpay_order_id,
                    razorpay_payment_id: razorpayResponse.razorpay_payment_id,
                    razorpay_signature: razorpayResponse.razorpay_signature,
                    order_id: orderId,
                    csrf_token: '<?php echo $_SESSION['csrf_token']; ?>'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?php echo APP_URL; ?>/payment-success.php?order_id=' + orderId;
                } else {
                    alert(data.message || 'Payment verification failed');
                    payButton.disabled = false;
                    payButton.innerHTML = '<i class="fas fa-lock"></i> Pay Now';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Payment verification failed. Please contact support.');
                payButton.disabled = false;
                payButton.innerHTML = '<i class="fas fa-lock"></i> Pay Now';
            });
        }
    </script>
</body>
</html>
