<?php
/**
 * Products Listing Page
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

$product = new Product();
$category = new Category();

// Get filters from query string
$filters = [];
$currentCategory = null;
if (isset($_GET['category'])) {
    $cat = $category->getCategoryBySlug($_GET['category']);
    if ($cat) {
        $filters['category_id'] = $cat['category_id'];
        $currentCategory = $cat;
    }
}

if (isset($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

if (isset($_GET['featured'])) {
    $filters['is_featured'] = 1;
}

if (isset($_GET['min_price'])) {
    $filters['min_price'] = floatval($_GET['min_price']);
}

if (isset($_GET['max_price'])) {
    $filters['max_price'] = floatval($_GET['max_price']);
}

if (isset($_GET['sort'])) {
    $filters['sort'] = $_GET['sort'];
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = PRODUCTS_PER_PAGE;

// Get products
$result = $product->getAllProducts($filters, $page, $limit);
$products = $result['products'];
$totalPages = $result['total_pages'];

// Get all active categories for sidebar
$categories = $category->getAllCategories(true);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    
    <div class="container" style="padding: 3rem 1.5rem;">
        <div class="products-page">
            <!-- Sidebar Filters -->
            <aside class="filters-sidebar">
                <h3>Filters</h3>
                
                <!-- Search -->
                <div class="filter-section">
                    <h4><i class="fas fa-search"></i> Search</h4>
                    <form method="GET" action="">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" 
                               class="filter-input">
                        <button type="submit" class="btn btn-primary btn-sm">Search</button>
                    </form>
                </div>
                
                <!-- Categories -->
                <div class="filter-section">
                    <h4><i class="fas fa-tags"></i> Categories</h4>
                    <ul class="filter-list">
                        <li><a href="products.php" <?php echo !isset($_GET['category']) ? 'class="active"' : ''; ?>>All Categories</a></li>
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="?category=<?php echo $cat['category_slug']; ?>" 
                               <?php echo (isset($_GET['category']) && $_GET['category'] === $cat['category_slug']) ? 'class="active"' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category_name']); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                
                <!-- Price Range -->
                <div class="filter-section">
                    <h4><i class="fas fa-rupee-sign"></i> Price Range</h4>
                    <form method="GET" action="">
                        <?php if (isset($_GET['category'])): ?>
                        <input type="hidden" name="category" value="<?php echo $_GET['category']; ?>">
                        <?php endif; ?>
                        <div style="display: flex; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <input type="number" name="min_price" placeholder="Min" class="filter-input" 
                                   value="<?php echo $_GET['min_price'] ?? ''; ?>">
                            <input type="number" name="max_price" placeholder="Max" class="filter-input"
                                   value="<?php echo $_GET['max_price'] ?? ''; ?>">
                        </div>
                        <button type="submit" class="btn btn-outline btn-sm">Apply</button>
                    </form>
                </div>
            </aside>
            
            <!-- Products Content -->
            <div class="products-content">
                <!-- Header -->
                <div class="products-header">
                    <div>
                        <h1>
                            <?php 
                            if (isset($_GET['search'])) {
                                echo 'Search: "' . htmlspecialchars($_GET['search']) . '"';
                            } elseif ($currentCategory) {
                                echo htmlspecialchars($currentCategory['category_name']);
                            } elseif (isset($_GET['featured'])) {
                                echo 'Featured Products';
                            } else {
                                echo 'All Products';
                            }
                            ?>
                        </h1>
                        <p><?php echo $result['total']; ?> products found</p>
                    </div>
                    
                    <!-- Sort -->
                    <div class="sort-dropdown">
                        <label for="sort">Sort by:</label>
                        <select id="sort" onchange="window.location.href='?<?php echo http_build_query(array_merge($_GET, ['sort' => ''])); ?>&sort=' + this.value">
                            <option value="">Newest</option>
                            <option value="price_low" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                            <option value="price_high" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                            <option value="popular" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'popular') ? 'selected' : ''; ?>>Most Popular</option>
                            <option value="rating" <?php echo (isset($_GET['sort']) && $_GET['sort'] === 'rating') ? 'selected' : ''; ?>>Highest Rated</option>
                        </select>
                    </div>
                </div>
                
                <!-- Products Grid -->
                <?php if (empty($products)): ?>
                <div class="no-products">
                    <i class="fas fa-box-open"></i>
                    <h3>No products found</h3>
                    <p>Try adjusting your filters or search criteria</p>
                    <a href="products.php" class="btn btn-primary">View All Products</a>
                </div>
                <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $prod): ?>
                    <div class="product-card" onclick="window.location.href='product.php?slug=<?php echo $prod['product_slug']; ?>'" style="cursor: pointer;">
                        <div class="product-image">
                            <img src="<?php echo $prod['thumbnail_image'] ? APP_URL . '/uploads/products/' . $prod['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($prod['product_name']); ?>">
                            <?php if ($prod['discount_price']): ?>
                            <span class="badge badge-sale">Sale</span>
                            <?php endif; ?>
                            <?php if ($prod['is_featured']): ?>
                            <span class="badge badge-featured">Featured</span>
                            <?php endif; ?>
                        </div>
                        <div class="product-info">
                            <p class="product-category"><?php echo htmlspecialchars($prod['category_name']); ?></p>
                            <h3 class="product-title">
                                <a href="product.php?slug=<?php echo $prod['product_slug']; ?>">
                                    <?php echo htmlspecialchars($prod['product_name']); ?>
                                </a>
                            </h3>
                            <!-- Single-vendor: hide store attribution -->
                            <div class="product-rating">
                                <?php 
                                $rating = floor($prod['rating_average']);
                                for ($i = 1; $i <= 5; $i++): 
                                ?>
                                    <i class="fas fa-star <?php echo $i <= $rating ? 'active' : ''; ?>"></i>
                                <?php endfor; ?>
                                <span>(<?php echo $prod['total_reviews']; ?>)</span>
                            </div>
                            <div class="product-footer">
                                <div class="product-price">
                                    <?php if ($prod['discount_price']): ?>
                                    <span class="price-original"><?php echo Utils::formatCurrency($prod['price']); ?></span>
                                    <span class="price-current"><?php echo Utils::formatCurrency($prod['discount_price']); ?></span>
                                    <?php else: ?>
                                    <span class="price-current"><?php echo Utils::formatCurrency($prod['price']); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if (User::isLoggedIn()): ?>
                                <button class="btn-cart" onclick="event.stopPropagation(); addToCart(<?php echo $prod['product_id']; ?>)" title="Add to Cart">
                                    <i class="fas fa-shopping-cart"></i>
                                </button>
                                <?php else: ?>
                                <a href="login.php" class="btn-cart" onclick="event.stopPropagation();" title="Login to buy">
                                    <i class="fas fa-lock"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" class="btn btn-outline">
                        <i class="fas fa-chevron-left"></i> Previous
                    </a>
                    <?php endif; ?>
                    
                    <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                    
                    <?php if ($page < $totalPages): ?>
                    <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" class="btn btn-outline">
                        Next <i class="fas fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script src="<?php echo JS_URL; ?>/main.js"></script>
</body>
</html>

<style>
.products-page {
    display: grid;
    grid-template-columns: 280px 1fr;
    gap: 2rem;
}

.filters-sidebar {
    background: var(--bg-card);
    padding: 1.5rem;
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-md);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.filters-sidebar h3 {
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    color: var(--text-primary);
}

.filter-section {
    margin-bottom: 2rem;
}

.filter-section h4 {
    font-size: 1rem;
    margin-bottom: 1rem;
    color: var(--text-primary);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.filter-input {
    width: 100%;
    padding: 0.5rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: var(--bg-secondary);
    color: var(--text-primary);
    margin-bottom: 0.5rem;
}

.filter-list {
    list-style: none;
}

.filter-list li {
    margin-bottom: 0.5rem;
}

.filter-list a {
    color: var(--text-secondary);
    padding: 0.5rem;
    display: block;
    border-radius: var(--radius-md);
    transition: var(--transition);
}

.filter-list a:hover,
.filter-list a.active {
    background: var(--bg-hover);
    color: var(--neon-green);
}

.products-content {
    min-height: 500px;
}

.products-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.products-header h1 {
    font-size: 2rem;
    margin-bottom: 0.5rem;
}

.products-header p {
    color: var(--text-secondary);
}

.sort-dropdown {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sort-dropdown select {
    padding: 0.5rem 1rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    cursor: pointer;
    background: var(--bg-secondary);
    color: var(--text-primary);
}

.no-products {
    text-align: center;
    padding: 4rem 2rem;
    background: var(--bg-card);
    border-radius: var(--radius-lg);
}

.no-products i {
    font-size: 4rem;
    color: var(--text-secondary);
    margin-bottom: 1rem;
}

.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 3rem;
}

.page-info {
    color: var(--text-secondary);
    font-weight: 500;
}

@media (max-width: 768px) {
    .products-page {
        grid-template-columns: 1fr;
    }
    
    .filters-sidebar {
        position: static;
    }
    
    .products-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
}
</style>
