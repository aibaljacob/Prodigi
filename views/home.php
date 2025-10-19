<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PRODIGI - Digital Products Marketplace</title>
        <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="home">
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Premium <span class="highlight">Digital Products</span></h1>
                <p>Curated downloads from the store owner. Browse, buy, and get instant access—simple and secure.</p>
                <div class="hero-buttons">
                    <a href="products.php" class="btn btn-secondary btn-lg">Explore Products</a>
                    
                </div>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
        <section>
        <div class="container">
            <h2 class="section-title">Browse Categories</h2>
            <p class="section-subtitle">Explore our diverse collection of digital products across multiple categories</p>
            <div class="categories-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                <?php $i = 0; foreach ($categories as $category): $i++; 
                    $accents = ['accent-cyan','accent-blue','accent-purple','accent-pink','accent-amber','accent-teal'];
                    $accentClass = $accents[($i - 1) % count($accents)];
                ?>
                <a href="products.php?category=<?php echo $category['category_slug']; ?>" class="category-card <?php echo $accentClass; ?>" style="padding: 1.5rem; text-align: left;">
                    <h3 class="category-name" style="font-size: 1.125rem; margin-bottom: 0.5rem;"><?php echo htmlspecialchars($category['category_name']); ?></h3>
                    <p class="category-count" style="font-size: 0.875rem; margin: 0;"><?php echo htmlspecialchars($category['category_description'] ?? 'Discover products'); ?></p>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Featured Products -->
        <section>
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Products</h2>
                    <a href="products.php?featured=1" class="btn btn-ghost">View All <i class="fas fa-arrow-right"></i></a>
            </div>
                <p class="section-subtitle">Hand-picked downloads from our store owner</p>
            <div class="products-grid">
                <?php foreach ($featuredProducts as $product): ?>
                <div class="product-card" onclick="window.location.href='product.php?slug=<?php echo $product['product_slug']; ?>'" style="cursor: pointer;">
                    <img class="product-img" src="<?php echo $product['thumbnail_image'] ? APP_URL . '/' . $product['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                         alt="<?php echo htmlspecialchars($product['product_name']); ?>">
                    <?php if ($product['discount_price']): ?>
                        <span class="product-badge">Sale</span>
                    <?php else: ?>
                        <span class="product-badge">Featured</span>
                    <?php endif; ?>
                    <div class="product-info">
                        <h3 class="product-title">
                            <?php echo htmlspecialchars($product['product_name']); ?>
                        </h3>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.5rem 0;">
                            <?php echo htmlspecialchars(Utils::truncate($product['short_description'] ?? '', 80)); ?>
                        </p>
                        <div class="product-rating">
                            <?php 
                            $rating = floor($product['rating_average']);
                            for ($i = 1; $i <= 5; $i++): 
                            ?>
                                <i class="fas fa-star <?php echo $i <= $rating ? '' : 'text-muted'; ?>"></i>
                            <?php endfor; ?>
                            <span>(<?php echo $product['total_reviews']; ?>)</span>
                        </div>
                        <div class="product-price" style="margin-top: 1rem;">
                            <?php if ($product['discount_price']): ?>
                            <span class="price-original"><?php echo Utils::formatCurrency($product['price']); ?></span>
                            <span class="price-current"><?php echo Utils::formatCurrency($product['discount_price']); ?></span>
                            <?php else: ?>
                            <span class="price-current"><?php echo Utils::formatCurrency($product['price']); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="how-it-works">
        <div class="container">
            <h2 class="section-title">How It Works</h2>
            <div class="steps-grid">
                <div class="step-card">
                    <div class="step-number">1</div>
                    <div class="step-icon"><i class="fas fa-compass"></i></div>
                    <h3>Browse</h3>
                    <p>Explore categories and discover premium digital files</p>
                </div>
                <div class="step-card">
                    <div class="step-number">2</div>
                    <div class="step-icon"><i class="fas fa-cart-plus"></i></div>
                    <h3>Add to Cart</h3>
                    <p>Pick what you like and proceed to checkout</p>
                </div>
                <div class="step-card">
                    <div class="step-number">3</div>
                    <div class="step-icon"><i class="fas fa-shield-check"></i></div>
                    <h3>Pay Securely</h3>
                    <p>Complete payment with trusted gateways</p>
                </div>
                <div class="step-card">
                    <div class="step-number">4</div>
                    <div class="step-icon"><i class="fas fa-download"></i></div>
                    <h3>Download Instantly</h3>
                    <p>Access your files immediately after purchase</p>
                </div>
            </div>
        </div>
    </section>

    

    <?php include VIEWS_PATH . '/includes/footer.php'; ?>

    <script src="<?php echo JS_URL; ?>/main.js"></script>
</body>
</html>
