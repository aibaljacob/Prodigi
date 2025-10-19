<?php
/**
 * Shopping Cart Page
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
$cartItems = $cart->getCartItems($userId);
$cartTotal = $cart->getCartTotal($userId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .cart-container {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            padding: 3rem 1.5rem;
        }
        
        .cart-items {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 2rem;
        }
        
        .cart-item {
            display: grid;
            grid-template-columns: 120px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .cart-item:last-child {
            border-bottom: none;
        }
        
        .cart-item-image {
            width: 120px;
            height: 120px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }
        
        .cart-item-details {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .cart-item-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .cart-item-title a {
            color: var(--text-primary);
            transition: color 0.3s ease;
        }
        
        .cart-item-title a:hover {
            color: var(--neon-green);
        }
        
        .cart-item-category {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .cart-item-price {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--neon-green);
        }
        
        .cart-item-actions {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
        }
        
        .cart-item-remove {
            background: rgba(255, 50, 50, 0.1);
            color: #ff3232;
            border: 1px solid #ff3232;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-sm);
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .cart-item-remove:hover {
            background: rgba(255, 50, 50, 0.2);
            box-shadow: 0 0 15px rgba(255, 50, 50, 0.3);
        }
        
        .cart-summary {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 2rem;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .cart-summary h2 {
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }
        
        .cart-summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .cart-summary-row:last-of-type {
            border-bottom: 2px solid var(--neon-green);
            padding-top: 1rem;
            margin-top: 1rem;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .cart-summary-label {
            color: var(--text-secondary);
        }
        
        .cart-summary-value {
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .cart-summary-total {
            color: var(--neon-green);
        }
        
        .cart-actions {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-cart-icon {
            font-size: 4rem;
            color: var(--text-muted);
            margin-bottom: 1rem;
        }
        
        .empty-cart h2 {
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: 1rem;
        }
        
        .empty-cart p {
            color: var(--text-secondary);
            margin-bottom: 2rem;
        }
        
        @media (max-width: 768px) {
            .cart-container {
                grid-template-columns: 1fr;
            }
            
            .cart-item {
                grid-template-columns: 80px 1fr;
            }
            
            .cart-item-image {
                width: 80px;
                height: 80px;
            }
            
            .cart-item-actions {
                grid-column: 2;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                margin-top: 1rem;
            }
            
            .cart-summary {
                position: relative;
                top: 0;
            }
        }
    </style>
</head>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="cart-container">
            <?php if (empty($cartItems)): ?>
                <!-- Empty Cart -->
                <div class="cart-items" style="grid-column: 1 / -1;">
                    <div class="empty-cart">
                        <div class="empty-cart-icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <h2>Your cart is empty</h2>
                        <p>Add some products to your cart and they will appear here</p>
                        <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-bag"></i> Browse Products
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <!-- Cart Items -->
                <div class="cart-items">
                    <h1 style="margin-bottom: 2rem; color: var(--text-primary);">Shopping Cart (<?php echo count($cartItems); ?> items)</h1>
                    
                    <?php foreach ($cartItems as $item): ?>
                    <div class="cart-item" data-cart-id="<?php echo $item['cart_id']; ?>">
                        <img src="<?php echo $item['thumbnail_image'] ? APP_URL . '/' . $item['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                             alt="<?php echo htmlspecialchars($item['product_name']); ?>"
                             class="cart-item-image">
                        
                        <div class="cart-item-details">
                            <div>
                                <h3 class="cart-item-title">
                                    <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo $item['product_slug']; ?>">
                                        <?php echo htmlspecialchars($item['product_name']); ?>
                                    </a>
                                </h3>
                                <p class="cart-item-category">
                                    <i class="fas fa-tag"></i> <?php echo htmlspecialchars($item['category_name']); ?>
                                </p>
                            </div>
                            <div class="cart-item-price">
                                <?php if ($item['discount_price']): ?>
                                    <span style="text-decoration: line-through; color: var(--text-muted); font-size: 1rem; margin-right: 0.5rem;">
                                        <?php echo Utils::formatCurrency($item['price']); ?>
                                    </span>
                                    <?php echo Utils::formatCurrency($item['discount_price']); ?>
                                <?php else: ?>
                                    <?php echo Utils::formatCurrency($item['price']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="cart-item-actions">
                            <button class="cart-item-remove" onclick="removeFromCart(<?php echo $item['cart_id']; ?>)">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Cart Summary -->
                <div class="cart-summary">
                    <h2>Order Summary</h2>
                    
                    <div class="cart-summary-row">
                        <span class="cart-summary-label">Subtotal:</span>
                        <span class="cart-summary-value"><?php echo Utils::formatCurrency($cartTotal); ?></span>
                    </div>
                    
                    <div class="cart-summary-row">
                        <span class="cart-summary-label">Items:</span>
                        <span class="cart-summary-value"><?php echo count($cartItems); ?></span>
                    </div>
                    
                    <div class="cart-summary-row">
                        <span class="cart-summary-label">Total:</span>
                        <span class="cart-summary-value cart-summary-total"><?php echo Utils::formatCurrency($cartTotal); ?></span>
                    </div>
                    
                    <div class="cart-actions">
                        <a href="<?php echo APP_URL; ?>/checkout.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-lock"></i> Proceed to Checkout
                        </a>
                        <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-outline">
                            <i class="fas fa-arrow-left"></i> Continue Shopping
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
        function removeFromCart(cartId) {
            if (!confirm('Remove this product from cart?')) {
                return;
            }
            
            fetch('<?php echo APP_URL; ?>/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'remove',
                    cart_id: cartId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Failed to remove item');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
    </script>
</body>
</html>
