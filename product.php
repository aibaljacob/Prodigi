<?php
/**
 * Product Detail Page
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Get product by slug or ID
$productSlug = $_GET['slug'] ?? '';
if (empty($productSlug)) {
    header('Location: ' . APP_URL . '/products.php');
    exit;
}

$product = new Product();
$category = new Category();

// Get product details
$productData = $product->getProductBySlug($productSlug);

if (!$productData) {
    header('Location: ' . APP_URL . '/products.php');
    exit;
}

// Get category details
$categoryData = $category->getCategoryById($productData['category_id']);

// Get related products from same category
$relatedResult = $product->getAllProducts(['category_id' => $productData['category_id']], 1, 4);
$relatedProducts = array_filter($relatedResult['products'], function($p) use ($productData) {
    return $p['product_id'] != $productData['product_id'];
});
$relatedProducts = array_slice($relatedProducts, 0, 3);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($productData['product_name']); ?> - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }
        
        .product-image-section {
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .product-main-image {
            width: 100%;
            aspect-ratio: 1;
            object-fit: cover;
            border-radius: var(--radius-md);
            border: 1px solid var(--border-color);
            background: var(--bg-secondary);
        }
        
        .product-info-section {
            color: var(--text-primary);
        }
        
        .product-breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            font-size: 14px;
            color: var(--text-muted);
        }
        
        .product-breadcrumb a {
            color: var(--text-muted);
            transition: color 0.3s ease;
        }
        
        .product-breadcrumb a:hover {
            color: var(--neon-green);
        }
        
        .product-detail-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--text-primary);
        }
        
        .product-detail-rating {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .product-detail-rating .stars {
            display: flex;
            gap: 0.25rem;
        }
        
        .product-detail-rating .stars i {
            color: var(--neon-green);
        }
        
        .product-detail-rating .stars i.text-muted {
            color: var(--text-muted);
        }
        
        .product-detail-price {
            margin-bottom: 2rem;
        }
        
        .product-price-current {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--neon-green);
        }
        
        .product-price-original {
            font-size: 1.5rem;
            color: var(--text-muted);
            text-decoration: line-through;
            margin-left: 1rem;
        }
        
        .product-discount-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: rgba(255, 50, 50, 0.1);
            color: #ff3232;
            border: 1px solid #ff3232;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            margin-left: 1rem;
        }
        
        .product-short-desc {
            font-size: 1.1rem;
            color: var(--text-secondary);
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        
        .product-actions {
            display: flex;
            gap: 1rem;
            margin-bottom: 3rem;
        }
        
        .product-actions .btn {
            flex: 1;
            padding: 1rem 2rem;
            font-size: 1.1rem;
        }
        
        .product-meta {
            display: grid;
            gap: 1rem;
            margin-bottom: 3rem;
            padding: 1.5rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
        }
        
        .product-meta-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--border-color);
        }
        
        .product-meta-item:last-child {
            border-bottom: none;
        }
        
        .product-meta-item i {
            color: var(--neon-green);
            font-size: 1.2rem;
            width: 24px;
        }
        
        .product-meta-label {
            font-weight: 600;
            color: var(--text-primary);
            min-width: 120px;
        }
        
        .product-meta-value {
            color: var(--text-secondary);
        }
        
        .product-description {
            margin-top: 3rem;
            padding-top: 3rem;
            border-top: 1px solid var(--border-color);
        }
        
        .product-description h2 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            color: var(--text-primary);
        }
        
        .product-description-content {
            color: var(--text-secondary);
            line-height: 1.8;
        }
        
        .related-products {
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid var(--border-color);
        }
        
        .related-products h2 {
            font-size: 1.8rem;
            margin-bottom: 2rem;
            color: var(--text-primary);
        }
        
        @media (max-width: 768px) {
            .product-detail {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
            
            .product-image-section {
                position: relative;
                top: 0;
            }
            
            .product-detail-title {
                font-size: 1.8rem;
            }
            
            .product-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    
    <div class="container" style="padding: 3rem 1.5rem;">
        <!-- Breadcrumb -->
        <div class="product-breadcrumb">
            <a href="<?php echo APP_URL; ?>">Home</a>
            <i class="fas fa-chevron-right"></i>
            <a href="<?php echo APP_URL; ?>/products.php">Products</a>
            <?php if ($categoryData): ?>
            <i class="fas fa-chevron-right"></i>
            <a href="<?php echo APP_URL; ?>/products.php?category=<?php echo $categoryData['category_slug']; ?>">
                <?php echo htmlspecialchars($categoryData['category_name']); ?>
            </a>
            <?php endif; ?>
            <i class="fas fa-chevron-right"></i>
            <span><?php echo htmlspecialchars($productData['product_name']); ?></span>
        </div>
        
        <!-- Product Detail -->
        <div class="product-detail">
            <!-- Product Image -->
            <div class="product-image-section">
                <img src="<?php echo $productData['thumbnail_image'] ? APP_URL . '/uploads/products/' . $productData['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                     alt="<?php echo htmlspecialchars($productData['product_name']); ?>"
                     class="product-main-image">
            </div>
            
            <!-- Product Info -->
            <div class="product-info-section">
                <h1 class="product-detail-title"><?php echo htmlspecialchars($productData['product_name']); ?></h1>
                
                <!-- Rating -->
                <div class="product-detail-rating">
                    <div class="stars">
                        <?php 
                        $rating = floor($productData['rating_average'] ?? 0);
                        for ($i = 1; $i <= 5; $i++): 
                        ?>
                            <i class="fas fa-star <?php echo $i <= $rating ? '' : 'text-muted'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span>(<?php echo $productData['total_reviews'] ?? 0; ?> reviews)</span>
                    <span>|</span>
                    <span><?php echo $productData['total_sales'] ?? 0; ?> sales</span>
                </div>
                
                <!-- Price -->
                <div class="product-detail-price">
                    <?php if ($productData['discount_price']): ?>
                        <span class="product-price-current"><?php echo Utils::formatCurrency($productData['discount_price']); ?></span>
                        <span class="product-price-original"><?php echo Utils::formatCurrency($productData['price']); ?></span>
                        <?php 
                        $discount = round((($productData['price'] - $productData['discount_price']) / $productData['price']) * 100);
                        ?>
                        <span class="product-discount-badge"><?php echo $discount; ?>% OFF</span>
                    <?php else: ?>
                        <span class="product-price-current"><?php echo Utils::formatCurrency($productData['price']); ?></span>
                    <?php endif; ?>
                </div>
                
                <!-- Short Description -->
                <?php if (!empty($productData['short_description'])): ?>
                <p class="product-short-desc"><?php echo htmlspecialchars($productData['short_description']); ?></p>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <?php if (User::isLoggedIn()): ?>
                <div class="product-actions">
                    <button onclick="addToCart(<?php echo $productData['product_id']; ?>)" class="btn btn-primary btn-lg">
                        <i class="fas fa-shopping-cart"></i> Add to Cart
                    </button>
                    <button onclick="buyNow(<?php echo $productData['product_id']; ?>)" class="btn btn-secondary btn-lg">
                        <i class="fas fa-bolt"></i> Buy Now
                    </button>
                </div>
                <?php else: ?>
                <div class="product-actions">
                    <a href="<?php echo APP_URL; ?>/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-user"></i> Login to Purchase
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Product Meta -->
                <div class="product-meta">
                    <div class="product-meta-item">
                        <i class="fas fa-tag"></i>
                        <span class="product-meta-label">Category:</span>
                        <span class="product-meta-value">
                            <a href="<?php echo APP_URL; ?>/products.php?category=<?php echo $categoryData['category_slug']; ?>">
                                <?php echo htmlspecialchars($categoryData['category_name']); ?>
                            </a>
                        </span>
                    </div>
                    
                    <?php if (!empty($productData['file_type'])): ?>
                    <div class="product-meta-item">
                        <i class="fas fa-file"></i>
                        <span class="product-meta-label">File Type:</span>
                        <span class="product-meta-value"><?php echo htmlspecialchars($productData['file_type']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($productData['file_size_mb'])): ?>
                    <div class="product-meta-item">
                        <i class="fas fa-download"></i>
                        <span class="product-meta-label">File Size:</span>
                        <span class="product-meta-value"><?php echo number_format($productData['file_size_mb'], 2); ?> MB</span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="product-meta-item">
                        <i class="fas fa-shield-check"></i>
                        <span class="product-meta-label">Instant Download:</span>
                        <span class="product-meta-value">Available after purchase</span>
                    </div>
                    
                    <?php if ($productData['download_limit']): ?>
                    <div class="product-meta-item">
                        <i class="fas fa-redo"></i>
                        <span class="product-meta-label">Download Limit:</span>
                        <span class="product-meta-value"><?php echo $productData['download_limit']; ?> times</span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Full Description -->
        <?php if (!empty($productData['product_description'])): ?>
        <div class="product-description">
            <h2>Product Description</h2>
            <div class="product-description-content">
                <?php echo nl2br(htmlspecialchars($productData['product_description'])); ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="related-products">
            <h2>Related Products</h2>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="product-card">
                    <img class="product-img" 
                         src="<?php echo $relatedProduct['thumbnail_image'] ? APP_URL . '/uploads/products/' . $relatedProduct['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($relatedProduct['product_name']); ?>">
                    <?php if ($relatedProduct['discount_price']): ?>
                        <span class="product-badge">Sale</span>
                    <?php endif; ?>
                    <div class="product-info">
                        <h3 class="product-title">
                            <a href="product.php?slug=<?php echo $relatedProduct['product_slug']; ?>">
                                <?php echo htmlspecialchars($relatedProduct['product_name']); ?>
                            </a>
                        </h3>
                        <div class="product-footer">
                            <div class="product-price">
                                <?php if ($relatedProduct['discount_price']): ?>
                                <span class="price-original"><?php echo Utils::formatCurrency($relatedProduct['price']); ?></span>
                                <span class="price-current"><?php echo Utils::formatCurrency($relatedProduct['discount_price']); ?></span>
                                <?php else: ?>
                                <span class="price-current"><?php echo Utils::formatCurrency($relatedProduct['price']); ?></span>
                                <?php endif; ?>
                            </div>
                            <button class="btn-cart" onclick="addToCart(<?php echo $relatedProduct['product_id']; ?>)">
                                <i class="fas fa-shopping-cart"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
        function addToCart(productId) {
            <?php if (!User::isLoggedIn()): ?>
            window.location.href = '<?php echo APP_URL; ?>/login.php?redirect=' + encodeURIComponent(window.location.href);
            return;
            <?php endif; ?>
            
            fetch('<?php echo APP_URL; ?>/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Product added to cart!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to add to cart');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        }
        
        function buyNow(productId) {
            <?php if (!User::isLoggedIn()): ?>
            window.location.href = '<?php echo APP_URL; ?>/login.php?redirect=' + encodeURIComponent(window.location.href);
            return;
            <?php endif; ?>
            
            // Add to cart and redirect to checkout
            fetch('<?php echo APP_URL; ?>/api/cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'add',
                    product_id: productId,
                    quantity: 1
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = '<?php echo APP_URL; ?>/checkout.php';
                } else {
                    alert(data.message || 'Failed to add to cart');
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
