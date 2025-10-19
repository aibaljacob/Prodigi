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

// Get reviews for this product
$db = Database::getInstance();
$reviews = $db->fetchAll(
    "SELECT r.*, u.username, u.full_name 
     FROM reviews r 
     JOIN users u ON r.user_id = u.user_id 
     WHERE r.product_id = :product_id AND r.is_approved = 1 
     ORDER BY r.created_at DESC",
    ['product_id' => $productData['product_id']]
);

// Check if current user can review (must have purchased)
$canReview = false;
$hasReviewed = false;
if (User::isLoggedIn()) {
    $userId = $_SESSION['user_id'];
    $hasPurchased = $db->fetchOne(
        "SELECT COUNT(*) as count FROM transactions 
         WHERE buyer_id = :user_id AND product_id = :product_id AND payment_status = 'completed'",
        ['user_id' => $userId, 'product_id' => $productData['product_id']]
    );
    $canReview = $hasPurchased['count'] > 0;
    
    $existingReview = $db->fetchOne(
        "SELECT review_id FROM reviews WHERE user_id = :user_id AND product_id = :product_id",
        ['user_id' => $userId, 'product_id' => $productData['product_id']]
    );
    $hasReviewed = !empty($existingReview);
}
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
                <img src="<?php echo $productData['thumbnail_image'] ? APP_URL . '/' . $productData['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
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
        
        <!-- Reviews Section -->
        <div class="product-reviews" style="margin-top: 3rem;">
            <h2>Customer Reviews</h2>
            
            <?php if ($canReview && !$hasReviewed): ?>
            <!-- Add Review Form -->
            <div class="review-form-container" style="background: var(--bg-secondary); padding: 2rem; border-radius: var(--radius-md); margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1.5rem;">Write a Review</h3>
                <form id="reviewForm" style="max-width: 600px;">
                    <input type="hidden" name="product_id" value="<?php echo $productData['product_id']; ?>">
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Rating</label>
                        <div class="rating-input" style="display: flex; gap: 0.5rem; font-size: 1.5rem;">
                            <i class="far fa-star" data-rating="1" onclick="setRating(1)" style="cursor: pointer; color: var(--text-muted);"></i>
                            <i class="far fa-star" data-rating="2" onclick="setRating(2)" style="cursor: pointer; color: var(--text-muted);"></i>
                            <i class="far fa-star" data-rating="3" onclick="setRating(3)" style="cursor: pointer; color: var(--text-muted);"></i>
                            <i class="far fa-star" data-rating="4" onclick="setRating(4)" style="cursor: pointer; color: var(--text-muted);"></i>
                            <i class="far fa-star" data-rating="5" onclick="setRating(5)" style="cursor: pointer; color: var(--text-muted);"></i>
                        </div>
                        <input type="hidden" name="rating" id="ratingValue" required>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Review Title</label>
                        <input type="text" name="review_title" maxlength="200" placeholder="Sum up your experience" 
                               style="width: 100%; padding: 0.75rem; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary);" required>
                    </div>
                    
                    <div style="margin-bottom: 1.5rem;">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Your Review</label>
                        <textarea name="review_text" rows="4" placeholder="Tell us about your experience with this product" 
                                  style="width: 100%; padding: 0.75rem; background: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); resize: vertical;" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Submit Review
                    </button>
                </form>
            </div>
            <?php elseif ($hasReviewed): ?>
            <p style="padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius-md); margin-bottom: 2rem;">
                <i class="fas fa-check-circle" style="color: var(--neon-green);"></i> You have already reviewed this product.
            </p>
            <?php elseif (User::isLoggedIn()): ?>
            <p style="padding: 1rem; background: var(--bg-secondary); border-radius: var(--radius-md); margin-bottom: 2rem;">
                <i class="fas fa-info-circle"></i> Purchase this product to leave a review.
            </p>
            <?php endif; ?>
            
            <!-- Reviews List -->
            <?php if (!empty($reviews)): ?>
            <div class="reviews-list">
                <?php foreach ($reviews as $review): ?>
                <div class="review-item" style="background: var(--bg-secondary); padding: 1.5rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                        <div>
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star" style="color: <?php echo $i <= $review['rating'] ? 'var(--neon-green)' : 'var(--text-muted)'; ?>; font-size: 0.875rem;"></i>
                                <?php endfor; ?>
                            </div>
                            <h4 style="margin: 0; font-size: 1rem; font-weight: 600;"><?php echo htmlspecialchars($review['review_title']); ?></h4>
                        </div>
                        <span style="color: var(--text-muted); font-size: 0.875rem;"><?php echo Utils::timeAgo($review['created_at']); ?></span>
                    </div>
                    <p style="color: var(--text-secondary); margin-bottom: 0.75rem;"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                    <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.875rem; color: var(--text-muted);">
                        <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($review['full_name'] ?: $review['username']); ?></span>
                        <?php if ($review['is_verified_purchase']): ?>
                        <span style="color: var(--neon-green);"><i class="fas fa-check-circle"></i> Verified Purchase</span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <p style="text-align: center; padding: 2rem; color: var(--text-muted);">No reviews yet. Be the first to review this product!</p>
            <?php endif; ?>
        </div>
        
        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="related-products">
            <h2>Related Products</h2>
            <div class="products-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                <div class="product-card">
                    <img class="product-img" 
                         src="<?php echo $relatedProduct['thumbnail_image'] ? APP_URL . '/' . $relatedProduct['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
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
        let selectedRating = 0;
        
        function setRating(rating) {
            selectedRating = rating;
            document.getElementById('ratingValue').value = rating;
            
            // Update star display
            document.querySelectorAll('.rating-input i').forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas');
                    star.style.color = 'var(--neon-green)';
                } else {
                    star.classList.remove('fas');
                    star.classList.add('far');
                    star.style.color = 'var(--text-muted)';
                }
            });
        }
        
        // Handle review form submission
        <?php if ($canReview && !$hasReviewed): ?>
        document.getElementById('reviewForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (selectedRating === 0) {
                alert('Please select a rating');
                return;
            }
            
            const formData = new FormData(this);
            const data = {
                action: 'add_review',
                product_id: formData.get('product_id'),
                rating: formData.get('rating'),
                review_title: formData.get('review_title'),
                review_text: formData.get('review_text')
            };
            
            fetch('<?php echo APP_URL; ?>/api/reviews.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Review submitted successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Failed to submit review');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred');
            });
        });
        <?php endif; ?>
        
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
