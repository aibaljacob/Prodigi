<?php
/**
 * Admin - Categories Management
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!User::isAdmin()) {
    Utils::redirect(APP_URL . '/login.php', 'Access denied. Admin only.', 'error');
}

$admin = new Admin();
$stats = $admin->getDashboardStats();
$category = new Category();

// Handle category actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    Utils::validateCSRF($_POST['csrf_token'] ?? '');
    
    if ($_POST['action'] === 'add') {
        $data = [
            'category_name' => $_POST['category_name'] ?? '',
            'category_description' => $_POST['category_description'] ?? '',
            'parent_category_id' => !empty($_POST['parent_category_id']) ? intval($_POST['parent_category_id']) : null,
            'display_order' => !empty($_POST['display_order']) ? intval($_POST['display_order']) : 0,
            'is_active' => 1
        ];
        
        $result = $category->createCategory($data);
        Utils::setFlashMessage($result['message'] ?? 'Category created', $result['success'] ? 'success' : 'error');
        Utils::redirect(APP_URL . '/admin/categories.php');
    }
    
    if ($_POST['action'] === 'update' && isset($_POST['category_id'])) {
        $data = [
            'category_name' => $_POST['category_name'] ?? '',
            'category_description' => $_POST['category_description'] ?? '',
            'parent_category_id' => !empty($_POST['parent_category_id']) ? intval($_POST['parent_category_id']) : null,
            'display_order' => !empty($_POST['display_order']) ? intval($_POST['display_order']) : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $result = $category->updateCategory($_POST['category_id'], $data);
        Utils::setFlashMessage($result['message'] ?? 'Category updated', $result['success'] ? 'success' : 'error');
        Utils::redirect(APP_URL . '/admin/categories.php');
    }
    
    if ($_POST['action'] === 'delete' && isset($_POST['category_id'])) {
        $result = $category->deleteCategory($_POST['category_id']);
        Utils::setFlashMessage($result['message'] ?? 'Category deleted', $result['success'] ? 'success' : 'error');
        Utils::redirect(APP_URL . '/admin/categories.php');
    }
}

// Get edit category if ID provided
$editCategory = null;
if (isset($_GET['edit'])) {
    $editCategory = $category->getCategoryById($_GET['edit']);
}

// Get all categories
$categories = $category->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Categories - Admin - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include '../views/admin/sidebar.php'; ?>
    
    <div class="admin-main">
        <header class="admin-header">
            <h1><i class="fas fa-tags"></i> Manage Categories</h1>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </header>
        
        <?php Utils::displayFlashMessage(); ?>
        
        <div class="admin-content">
            <div class="dashboard-row">
                <!-- Add/Edit Form -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-<?php echo $editCategory ? 'edit' : 'plus'; ?>"></i> 
                            <?php echo $editCategory ? 'Edit' : 'Add New'; ?> Category
                        </h2>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="" class="form-vertical">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <input type="hidden" name="action" value="<?php echo $editCategory ? 'update' : 'add'; ?>">
                            <?php if ($editCategory): ?>
                            <input type="hidden" name="category_id" value="<?php echo $editCategory['category_id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="category_name">Category Name *</label>
                                <input type="text" id="category_name" name="category_name" required
                                       value="<?php echo htmlspecialchars($editCategory['category_name'] ?? ''); ?>"
                                       class="form-control">
                            </div>
                            
                            <div class="form-group">
                                <label for="category_description">Description</label>
                                <textarea id="category_description" name="category_description" rows="3"
                                          class="form-control"><?php echo htmlspecialchars($editCategory['category_description'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="parent_category_id">Parent Category (Optional)</label>
                                <select id="parent_category_id" name="parent_category_id" class="form-control">
                                    <option value="">None (Top Level)</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <?php if (!$editCategory || $cat['category_id'] != $editCategory['category_id']): ?>
                                        <option value="<?php echo $cat['category_id']; ?>"
                                                <?php echo ($editCategory && $editCategory['parent_category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cat['category_name']); ?>
                                        </option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="display_order">Display Order</label>
                                <input type="number" id="display_order" name="display_order" min="0"
                                       value="<?php echo $editCategory['display_order'] ?? 0; ?>"
                                       class="form-control">
                            </div>
                            
                            <?php if ($editCategory): ?>
                            <div class="form-group">
                                <label class="checkbox-label">
                                    <input type="checkbox" name="is_active" 
                                           <?php echo $editCategory['is_active'] ? 'checked' : ''; ?>>
                                    Active
                                </label>
                            </div>
                            <?php endif; ?>
                            
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> 
                                    <?php echo $editCategory ? 'Update' : 'Add'; ?> Category
                                </button>
                                <?php if ($editCategory): ?>
                                <a href="categories.php" class="btn btn-outline">Cancel</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Categories List -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-list"></i> All Categories (<?php echo count($categories); ?>)</h2>
                    </div>
                    <div class="card-body">
                        <?php if (empty($categories)): ?>
                            <p class="text-muted">No categories found</p>
                        <?php else: ?>
                            <div class="categories-list">
                                <?php foreach ($categories as $cat): ?>
                                <div class="category-item">
                                    <div class="category-info">
                                        <strong><?php echo htmlspecialchars($cat['category_name']); ?></strong>
                                        <small>
                                            Slug: <?php echo htmlspecialchars($cat['category_slug']); ?>
                                            <?php if ($cat['parent_id']): ?>
                                                | Parent: 
                                                <?php 
                                                $parent = array_filter($categories, fn($c) => $c['category_id'] == $cat['parent_id']);
                                                echo $parent ? htmlspecialchars(reset($parent)['category_name']) : 'Unknown';
                                                ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php if (!$cat['is_active']): ?>
                                            <span class="badge badge-failed">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="category-actions">
                                        <a href="?edit=<?php echo $cat['category_id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="category_id" value="<?php echo $cat['category_id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Delete this category? Products in this category will need reassignment.')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo JS_URL; ?>/admin.js"></script>
</body>
</html>

<style>
.dashboard-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
.form-vertical { display: flex; flex-direction: column; gap: 1rem; }
.form-group { display: flex; flex-direction: column; gap: 0.5rem; }
.form-group label { color: var(--text-primary); font-weight: 500; }
.form-control {
    padding: 0.75rem;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    background: var(--bg-secondary);
    color: var(--text-primary);
    font-family: inherit;
}
.form-control:focus {
    outline: none;
    border-color: var(--neon-green);
    box-shadow: 0 0 0 2px rgba(57, 255, 20, 0.1);
}
.checkbox-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: var(--text-primary);
    cursor: pointer;
}
.form-actions {
    display: flex;
    gap: 0.75rem;
    margin-top: 0.5rem;
}
.categories-list {
    display: flex;
    flex-direction: column;
    gap: 0.75rem;
}
.category-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem;
    background: var(--bg-secondary);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
}
.category-info {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}
.category-info small {
    color: var(--text-secondary);
    font-size: 0.8rem;
}
.category-actions {
    display: flex;
    gap: 0.5rem;
}
@media (max-width: 1024px) {
    .dashboard-row { grid-template-columns: 1fr; }
}
</style>
