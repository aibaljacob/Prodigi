<?php
/**
 * Admin - Products Management
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!User::isAdmin()) {
    Utils::redirect(APP_URL . '/login.php', 'Access denied. Admin only.', 'error');
}

$admin = new Admin();
$stats = $admin->getDashboardStats();
$product = new Product();
$category = new Category();

// Handle product actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    Utils::validateCSRF($_POST['csrf_token'] ?? '');
    
    if ($_POST['action'] === 'delete' && isset($_POST['product_id'])) {
        $result = $admin->deleteProduct($_POST['product_id']);
        Utils::setFlashMessage($result['message'] ?? 'Product deleted', $result['success'] ? 'success' : 'error');
        Utils::redirect(APP_URL . '/admin/products.php');
    }
    
    if ($_POST['action'] === 'toggle_featured' && isset($_POST['product_id'])) {
        $result = $product->toggleFeatured($_POST['product_id']);
        Utils::setFlashMessage('Featured status updated', $result['success'] ? 'success' : 'error');
        Utils::redirect(APP_URL . '/admin/products.php');
    }
}

// Get filters
$filters = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['category']) && !empty($_GET['category'])) {
    $filters['category_id'] = intval($_GET['category']);
}
if (isset($_GET['featured'])) {
    $filters['is_featured'] = 1;
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;

// Get products (including unapproved for admin view)
$result = $product->getAllProducts($filters, $page, $limit, true);
$products = $result['products'];
$totalPages = $result['total_pages'];

// Get all categories for filter
$categories = $category->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include '../views/admin/sidebar.php'; ?>
    
    <div class="admin-main">
        <header class="admin-header">
            <h1><i class="fas fa-box"></i> Manage Products</h1>
            <div class="admin-user">
                <a href="product-add.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Add Product
                </a>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </header>
        
        <?php Utils::displayFlashMessage(); ?>
        
        <div class="admin-content">
            <!-- Filters -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-filter"></i> Filters</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="filter-form">
                        <div class="filter-group">
                            <input type="text" name="search" placeholder="Search products..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="filter-input">
                            
                            <select name="category" class="filter-input">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['category_id']; ?>" 
                                        <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category_name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                            
                            <label class="checkbox-label">
                                <input type="checkbox" name="featured" value="1" 
                                       <?php echo isset($_GET['featured']) ? 'checked' : ''; ?>>
                                Featured Only
                            </label>
                            
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="products.php" class="btn btn-outline btn-sm">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Products Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Products (<?php echo $result['total']; ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <p class="text-muted">No products found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Sales</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $prod): ?>
                                    <tr>
                                        <td>#<?php echo $prod['product_id']; ?></td>
                                        <td>
                                            <div class="product-cell">
                                                <img src="<?php echo $prod['thumbnail_image'] ? APP_URL . '/uploads/images/' . $prod['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                                                     alt="" class="product-thumb">
                                                <div>
                                                    <strong><?php echo htmlspecialchars(Utils::truncate($prod['product_name'], 40)); ?></strong>
                                                    <?php if ($prod['is_featured']): ?>
                                                        <span class="badge badge-featured">Featured</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($prod['category_name']); ?></td>
                                        <td>
                                            <?php if ($prod['discount_price']): ?>
                                                <span style="text-decoration: line-through; color: var(--text-secondary);">
                                                    <?php echo Utils::formatCurrency($prod['price']); ?>
                                                </span><br>
                                                <strong><?php echo Utils::formatCurrency($prod['discount_price']); ?></strong>
                                            <?php else: ?>
                                                <?php echo Utils::formatCurrency($prod['price']); ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo number_format($prod['total_sales']); ?></td>
                                        <td>
                                            <?php if ($prod['is_active']): ?>
                                                <span class="badge badge-completed">Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-failed">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="product-edit.php?id=<?php echo $prod['product_id']; ?>" 
                                                   class="btn btn-sm btn-primary" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="toggle_featured">
                                                    <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-outline" title="Toggle Featured">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="product_id" value="<?php echo $prod['product_id']; ?>">
                                                    <button type="submit" class="btn btn-sm btn-danger" 
                                                            onclick="return confirm('Delete this product?')" title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                               class="btn btn-outline btn-sm">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                            <?php endif; ?>
                            
                            <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                            
                            <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                               class="btn btn-outline btn-sm">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo JS_URL; ?>/admin.js"></script>
</body>
</html>

<style>
.filter-form { width: 100%; }
.filter-group { display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center; }
.filter-input { 
    flex: 1; 
    min-width: 200px;
    padding: 0.5rem 0.75rem; 
    border: 1px solid var(--border-color); 
    border-radius: var(--radius-md); 
    background: var(--bg-secondary); 
    color: var(--text-primary);
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-primary);
    cursor: pointer;
}
.product-cell {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}
.product-thumb {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: var(--radius-sm);
}
.badge-featured {
    background: rgba(255, 200, 87, 0.15);
    color: var(--accent-amber);
    font-size: 0.7rem;
    padding: 0.1rem 0.4rem;
    border-radius: 999px;
    margin-left: 0.5rem;
}
.action-buttons {
    display: flex;
    gap: 0.5rem;
}
.table-responsive { overflow-x: auto; }
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
}
.page-info {
    color: var(--text-secondary);
    font-weight: 500;
}
</style>
