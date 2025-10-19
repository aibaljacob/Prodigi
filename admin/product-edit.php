<?php
/**
 * Admin - Edit Product
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!User::isAdmin()) {
    Utils::redirect(APP_URL . '/login.php', 'Access denied. Admin only.', 'error');
}

// Get product ID
if (!isset($_GET['id'])) {
    Utils::redirect(APP_URL . '/admin/products.php', 'Product ID is required', 'error');
}

$productId = intval($_GET['id']);

$admin = new Admin();
$stats = $admin->getDashboardStats();
$product = new Product();
$category = new Category();

// Get product details
$productData = $product->getProductById($productId);
if (!$productData) {
    Utils::redirect(APP_URL . '/admin/products.php', 'Product not found', 'error');
}

// Handle product update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        Utils::validateCSRF($_POST['csrf_token'] ?? '');
        
        // Validate required fields
        $required = ['product_name', 'category_id', 'price', 'description'];
        foreach ($required as $field) {
            if (empty($_POST[$field])) {
                throw new Exception(ucfirst(str_replace('_', ' ', $field)) . ' is required');
            }
        }
        
        // Get category to check allowed file types
        $categoryData = $category->getCategoryById($_POST['category_id']);
        if (!$categoryData) {
            throw new Exception('Invalid category selected');
        }
        
        // Check if category has file type restrictions
        $allowedFileTypes = null;
        if (!empty($categoryData['allowed_file_types'])) {
            $allowedFileTypes = json_decode($categoryData['allowed_file_types'], true);
        }
        
        // Handle thumbnail upload (if new file provided)
        $thumbnailPath = $productData['thumbnail_image'] ?? null;
        if (isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] === UPLOAD_ERR_OK) {
            $imageUploader = new FileUpload(
                UPLOAD_DIR . '/products',
                ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                5 * 1024 * 1024 // 5MB
            );
            
            $uploadResult = $imageUploader->upload($_FILES['thumbnail']);
            if (!$uploadResult['success']) {
                throw new Exception('Thumbnail upload failed: ' . $uploadResult['message']);
            }
            
            // Delete old thumbnail
            if ($thumbnailPath && file_exists(__DIR__ . '/../' . $thumbnailPath)) {
                @unlink(__DIR__ . '/../' . $thumbnailPath);
            }
            
            $thumbnailPath = 'uploads/products/' . $uploadResult['file_name'];
        }
        
        // Handle product file upload (if new file provided)
        $productFilePath = $productData['product_file_path'] ?? null;
        $productFileOriginalName = $productData['product_file_original_name'] ?? null;
        $productFileSize = $productData['product_file_size_bytes'] ?? null;
        
        if (isset($_FILES['product_file']) && $_FILES['product_file']['error'] === UPLOAD_ERR_OK) {
            // Use category's allowed file types or default to all types
            $fileTypes = $allowedFileTypes ?? [
                'application/pdf',
                'application/zip',
                'application/x-zip-compressed',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/msword',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'text/plain',
                'image/jpeg',
                'image/png',
                'video/mp4',
                'video/mpeg',
                'video/quicktime',
                'video/x-msvideo',
                'audio/mpeg',
                'audio/wav',
                'audio/ogg',
                'application/epub+zip'
            ];
            
            $fileUploader = new FileUpload(
                UPLOAD_DIR . '/files',
                $fileTypes,
                MAX_FILE_SIZE
            );
            
            $uploadResult = $fileUploader->upload($_FILES['product_file']);
            if (!$uploadResult['success']) {
                // Provide better error message if file type is not allowed
                $errorMsg = $uploadResult['message'];
                if ($allowedFileTypes && strpos($errorMsg, 'File type') !== false) {
                    $errorMsg = 'File type not allowed for this category. Allowed types: ' . implode(', ', array_map(function($type) {
                        return strtoupper(str_replace(['application/', 'video/', 'audio/', 'image/', 'text/'], '', $type));
                    }, $allowedFileTypes));
                }
                throw new Exception('Product file upload failed: ' . $errorMsg);
            }
            
            // Delete old product file
            if ($productFilePath && file_exists(__DIR__ . '/../' . $productFilePath)) {
                @unlink(__DIR__ . '/../' . $productFilePath);
            }
            
            $productFilePath = 'uploads/files/' . $uploadResult['file_name'];
            $productFileOriginalName = $uploadResult['file_original_name'];
            $productFileSize = $uploadResult['file_size_bytes'];
        }
        
        // Validate that product has a file (either existing or newly uploaded)
        if (empty($productFilePath)) {
            throw new Exception('Product file is required. Please upload a digital file for this product.');
        }
        
        // Prepare product data
        $updateData = [
            'category_id' => intval($_POST['category_id']),
            'product_name' => Utils::sanitize($_POST['product_name']),
            'product_description' => Utils::sanitize($_POST['description']),
            'short_description' => Utils::sanitize($_POST['short_description'] ?? ''),
            'price' => floatval($_POST['price']),
            'discount_price' => !empty($_POST['discount_price']) ? floatval($_POST['discount_price']) : null,
            'thumbnail_image' => $thumbnailPath,
            'product_file_path' => $productFilePath,
            'product_file_original_name' => $productFileOriginalName,
            'product_file_size_bytes' => $productFileSize,
            'is_featured' => isset($_POST['is_featured']) ? 1 : 0,
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        ];
        
        $result = $product->update($productId, $updateData);
        
        if ($result['success']) {
            Utils::setFlashMessage('Product updated successfully!', 'success');
            Utils::redirect(APP_URL . '/admin/products.php');
        } else {
            throw new Exception($result['message'] ?? 'Failed to update product');
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
        // Refresh product data if error occurred
        $productData = $product->getProductById($productId);
    }
}

// Get all categories
$categories = $category->getAllCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        .form-control {
            width: 100%;
            padding: 0.75rem;
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-primary);
            font-size: 0.95rem;
        }
        .form-control:focus {
            outline: none;
            border-color: var(--neon-green);
        }
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        .file-input-wrapper {
            position: relative;
            overflow: hidden;
            display: inline-block;
            width: 100%;
        }
        .file-input-btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: var(--bg-secondary);
            border: 1px dashed var(--border-color);
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            text-align: center;
            transition: all 0.3s ease;
        }
        .file-input-btn:hover {
            border-color: var(--neon-green);
            background: rgba(0, 255, 163, 0.05);
        }
        .file-input-wrapper input[type=file] {
            position: absolute;
            left: -9999px;
        }
        .file-name {
            margin-top: 0.5rem;
            font-size: 0.85rem;
            color: var(--text-secondary);
        }
        .current-file {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem;
            background: rgba(0, 255, 163, 0.05);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            margin-bottom: 0.5rem;
        }
        .current-file img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }
        .current-file-info {
            flex: 1;
        }
        .current-file-info strong {
            display: block;
            color: var(--neon-green);
            margin-bottom: 0.25rem;
        }
        .current-file-info small {
            color: var(--text-secondary);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 1rem 0;
        }
        .checkbox-group input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
        }
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid var(--border-color);
        }
        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid #ef4444;
            color: #ef4444;
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }
        .helper-text {
            font-size: 0.85rem;
            color: var(--text-secondary);
            margin-top: 0.25rem;
        }
        .product-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        .product-status.active {
            background: rgba(0, 255, 163, 0.1);
            color: var(--neon-green);
        }
        .product-status.inactive {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
    </style>
</head>
<body class="admin-body">
    <?php include '../views/admin/sidebar.php'; ?>
    
    <div class="admin-main">
        <header class="admin-header">
            <div>
                <h1><i class="fas fa-edit"></i> Edit Product</h1>
                <span class="product-status <?php echo $productData['is_active'] ? 'active' : 'inactive'; ?>">
                    <i class="fas fa-circle"></i>
                    <?php echo $productData['is_active'] ? 'Active' : 'Inactive'; ?>
                </span>
            </div>
            <div class="admin-user">
                <a href="products.php" class="btn btn-outline btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Products
                </a>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </header>
        
        <div class="admin-content">
            <?php if (isset($error)): ?>
            <div class="alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> Product Information</h2>
                </div>
                <div class="card-body">
                    <form method="POST" action="" enctype="multipart/form-data">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <!-- Basic Information -->
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="product_name">Product Name *</label>
                                <input type="text" id="product_name" name="product_name" required 
                                       class="form-control" placeholder="Enter product name"
                                       value="<?php echo htmlspecialchars($productData['product_name']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="category_id">Category *</label>
                                <select id="category_id" name="category_id" required class="form-control">
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"
                                            <?php echo ($productData['category_id'] == $cat['category_id']) ? 'selected' : ''; ?>>
                                        <?php 
                                        if (!empty($cat['parent_name'])) {
                                            echo htmlspecialchars($cat['parent_name']) . ' > ';
                                        }
                                        echo htmlspecialchars($cat['category_name']); 
                                        ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="short_description">Short Description</label>
                            <input type="text" id="short_description" name="short_description" 
                                   class="form-control" placeholder="Brief one-line description"
                                   maxlength="150" value="<?php echo htmlspecialchars($productData['short_description'] ?? ''); ?>">
                            <div class="helper-text">Max 150 characters</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Full Description *</label>
                            <textarea id="description" name="description" required 
                                      class="form-control" placeholder="Enter detailed product description"><?php echo htmlspecialchars($productData['product_description']); ?></textarea>
                        </div>
                        
                        <!-- Pricing -->
                        <h3 style="margin: 2rem 0 1rem; color: var(--neon-green);">
                            <i class="fas fa-dollar-sign"></i> Pricing
                        </h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="price">Regular Price (₹) *</label>
                                <input type="number" id="price" name="price" required min="0" step="0.01"
                                       class="form-control" placeholder="0.00"
                                       value="<?php echo htmlspecialchars($productData['price']); ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="discount_price">Discounted Price (₹)</label>
                                <input type="number" id="discount_price" name="discount_price" min="0" step="0.01"
                                       class="form-control" placeholder="Optional"
                                       value="<?php echo htmlspecialchars($productData['discount_price'] ?? ''); ?>">
                                <div class="helper-text">Leave empty if no discount</div>
                            </div>
                        </div>
                        
                        <!-- Files -->
                        <h3 style="margin: 2rem 0 1rem; color: var(--neon-green);">
                            <i class="fas fa-image"></i> Product Media
                        </h3>
                        
                        <div class="form-group">
                            <label>Thumbnail Image (Optional)</label>
                            
                            <?php if (!empty($productData['thumbnail_image'])): ?>
                            <div class="current-file">
                                <img src="<?php echo APP_URL . '/' . $productData['thumbnail_image']; ?>" 
                                     alt="Current thumbnail">
                                <div class="current-file-info">
                                    <strong>Current Thumbnail</strong>
                                    <small>Upload a new image to replace</small>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <div class="file-input-wrapper">
                                <label for="thumbnail" class="file-input-btn">
                                    <i class="fas fa-cloud-upload-alt"></i> 
                                    <?php echo !empty($productData['thumbnail_image']) ? 'Replace' : 'Upload'; ?> Thumbnail Image
                                </label>
                                <input type="file" id="thumbnail" name="thumbnail" accept="image/*" 
                                       onchange="updateFileName(this, 'thumbnail-name')">
                                <div id="thumbnail-name" class="file-name">No file chosen</div>
                            </div>
                            <div class="helper-text">JPG, PNG, GIF or WebP. Max 5MB. (Optional)</div>
                        </div>
                        
                        <div class="form-group">
                            <label>Product File (Digital Download) *</label>
                            
                            <?php if (!empty($productData['product_file_path'])): ?>
                            <div class="current-file">
                                <i class="fas fa-file" style="font-size: 2rem; color: var(--neon-cyan);"></i>
                                <div class="current-file-info">
                                    <strong><?php echo htmlspecialchars($productData['product_file_original_name'] ?? 'Product File'); ?></strong>
                                    <small><?php echo !empty($productData['product_file_size_bytes']) ? FileUpload::formatFileSize($productData['product_file_size_bytes']) : 'Unknown size'; ?></small>
                                </div>
                            </div>
                            <?php else: ?>
                            <div class="helper-text" style="color: var(--neon-yellow); margin-bottom: 1rem;">
                                <i class="fas fa-exclamation-triangle"></i> No product file uploaded yet. Please upload a file.
                            </div>
                            <?php endif; ?>
                            
                            <div class="file-input-wrapper">
                                <label for="product_file" class="file-input-btn">
                                    <i class="fas fa-file-upload"></i> 
                                    <?php echo !empty($productData['product_file_path']) ? 'Replace' : 'Upload'; ?> Product File
                                </label>
                                <input type="file" id="product_file" name="product_file"
                                       <?php echo empty($productData['product_file_path']) ? 'required' : ''; ?>
                                       onchange="updateFileName(this, 'file-name')">
                                <div id="file-name" class="file-name">No file chosen</div>
                            </div>
                            <div class="helper-text">
                                PDF, ZIP, Documents, Images, Videos, etc. Max <?php echo MAX_FILE_SIZE / (1024 * 1024 * 1024); ?>GB. (Required)
                            </div>
                        </div>
                        
                        <!-- Additional Options -->
                        <h3 style="margin: 2rem 0 1rem; color: var(--neon-green);">
                            <i class="fas fa-cog"></i> Additional Options
                        </h3>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                                   <?php echo !empty($productData['is_featured']) ? 'checked' : ''; ?>>
                            <label for="is_featured" style="margin: 0;">
                                <i class="fas fa-star" style="color: var(--neon-yellow);"></i> 
                                Mark as Featured Product
                            </label>
                        </div>
                        
                        <div class="checkbox-group">
                            <input type="checkbox" id="is_active" name="is_active" value="1"
                                   <?php echo !empty($productData['is_active']) ? 'checked' : ''; ?>>
                            <label for="is_active" style="margin: 0;">
                                <i class="fas fa-toggle-on" style="color: var(--neon-green);"></i> 
                                Product is Active
                            </label>
                        </div>
                        
                        <!-- Form Actions -->
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Product
                            </button>
                            <a href="products.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Product Stats -->
            <div class="dashboard-card" style="margin-top: 1.5rem;">
                <div class="card-header">
                    <h2><i class="fas fa-chart-line"></i> Product Statistics</h2>
                </div>
                <div class="card-body">
                    <div class="form-grid">
                        <div>
                            <label>Total Sales</label>
                            <div style="font-size: 1.5rem; color: var(--neon-green); font-weight: 600; margin-top: 0.5rem;">
                                <?php echo number_format($productData['sales_count'] ?? $productData['total_sales'] ?? 0); ?>
                            </div>
                        </div>
                        <div>
                            <label>Created On</label>
                            <div style="font-size: 1rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                <?php echo date('M d, Y', strtotime($productData['created_at'])); ?>
                            </div>
                        </div>
                        <div>
                            <label>Last Updated</label>
                            <div style="font-size: 1rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                <?php echo isset($productData['updated_at']) ? date('M d, Y', strtotime($productData['updated_at'])) : 'N/A'; ?>
                            </div>
                        </div>
                        <div>
                            <label>Product ID</label>
                            <div style="font-size: 1rem; color: var(--text-secondary); margin-top: 0.5rem;">
                                #<?php echo $productData['product_id']; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function updateFileName(input, displayId) {
            const display = document.getElementById(displayId);
            if (input.files && input.files.length > 0) {
                display.textContent = input.files[0].name;
                display.style.color = 'var(--neon-green)';
            } else {
                display.textContent = 'No file chosen';
                display.style.color = 'var(--text-secondary)';
            }
        }
    </script>
</body>
</html>
