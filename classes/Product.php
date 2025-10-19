<?php
/**
 * Product Class - Handles digital product operations
 * Implements OOP principles
 */

class Product {
    private $db;
    private $productId;
    private $storeId;
    private $categoryId;
    private $productName;
    private $productSlug;
    private $price;
    private $discountPrice;
    
    /**
     * Constructor
     * @param int|null $productId
     */
    public function __construct($productId = null) {
        $this->db = Database::getInstance();
        
        if ($productId) {
            $this->loadProduct($productId);
        }
    }

    private function assertAdminWrite() {
        if (defined('SINGLE_VENDOR') && SINGLE_VENDOR) {
            if (!User::isAdmin()) {
                throw new Exception('Only admin can manage products in single-vendor mode');
            }
        }
    }
    
    /**
     * Load product data
     * @param int $productId
     * @return bool
     */
    private function loadProduct($productId) {
        $query = "SELECT p.*, s.store_name, s.store_slug, c.category_name 
                  FROM products p
                  JOIN stores s ON p.store_id = s.store_id
                  JOIN categories c ON p.category_id = c.category_id
                  WHERE p.product_id = :product_id";
        $product = $this->db->fetchOne($query, ['product_id' => $productId]);
        
        if ($product) {
            foreach ($product as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->$key = $value;
                }
            }
            return true;
        }
        
        return false;
    }
    
    /**
     * Create new product
     * @param array $data
     * @return array
     */
    public function createProduct($data) {
        try {
            $this->assertAdminWrite();
            // Generate unique slug
            $slug = $this->generateSlug($data['product_name']);
            $counter = 1;
            $originalSlug = $slug;
            while ($this->slugExists($slug)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // In single-vendor mode, admin products are auto-approved
            $isApproved = (defined('SINGLE_VENDOR') && SINGLE_VENDOR && User::isAdmin()) ? 1 : (REQUIRE_PRODUCT_APPROVAL ? 0 : 1);
            $approvalStatus = $isApproved ? 'approved' : 'pending';
            
            $insertData = [
                'store_id' => $data['store_id'],
                'category_id' => $data['category_id'],
                'product_name' => $data['product_name'],
                'product_slug' => $slug,
                'product_description' => $data['product_description'] ?? '',
                'short_description' => $data['short_description'] ?? '',
                'price' => $data['price'],
                'discount_price' => $data['discount_price'] ?? null,
                'thumbnail_image' => $data['thumbnail_image'] ?? null,
                'product_file_path' => $data['product_file_path'] ?? null,
                'product_file_original_name' => $data['product_file_original_name'] ?? null,
                'product_file_size_bytes' => $data['product_file_size_bytes'] ?? null,
                'product_tags' => $data['product_tags'] ?? '',
                'is_featured' => $data['is_featured'] ?? 0,
                'is_active' => $data['is_active'] ?? 1,
                'file_type' => $data['file_type'] ?? '',
                'approval_status' => $approvalStatus,
                'is_approved' => $isApproved
            ];
            $productId = $this->db->insert('products', $insertData);
            
            return [
                'success' => true,
                'message' => 'Product created successfully!',
                'product_id' => $productId,
                'product_slug' => $slug
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Product creation failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get all products with filters
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @param bool $includeUnapproved - For admin panel to show all products
     * @return array
     */
    public function getAllProducts($filters = [], $page = 1, $limit = 12, $includeUnapproved = false) {
        $offset = ($page - 1) * $limit;
        $where = ['p.is_active = 1'];
        
        // Only filter by approval status if not explicitly including unapproved
        if (!$includeUnapproved) {
            $where[] = 'p.is_approved = 1';
        }
        
        $params = [];
        
        if (isset($filters['category_id'])) {
            $where[] = 'p.category_id = :category_id';
            $params['category_id'] = $filters['category_id'];
        }
        
        if (isset($filters['store_id'])) {
            $where[] = 'p.store_id = :store_id';
            $params['store_id'] = $filters['store_id'];
        }
        
        if (isset($filters['search'])) {
            $where[] = '(p.product_name LIKE :search OR p.product_description LIKE :search OR p.product_tags LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        if (isset($filters['is_featured'])) {
            $where[] = 'p.is_featured = :is_featured';
            $params['is_featured'] = $filters['is_featured'];
        }
        
        if (isset($filters['min_price'])) {
            $where[] = 'p.price >= :min_price';
            $params['min_price'] = $filters['min_price'];
        }
        
        if (isset($filters['max_price'])) {
            $where[] = 'p.price <= :max_price';
            $params['max_price'] = $filters['max_price'];
        }
        
        $whereString = implode(' AND ', $where);
        
        $orderBy = 'p.created_at DESC';
        if (isset($filters['sort'])) {
            switch ($filters['sort']) {
                case 'price_low':
                    $orderBy = 'p.price ASC';
                    break;
                case 'price_high':
                    $orderBy = 'p.price DESC';
                    break;
                case 'popular':
                    $orderBy = 'p.total_sales DESC';
                    break;
                case 'rating':
                    $orderBy = 'p.rating_average DESC';
                    break;
            }
        }
        
    $query = "SELECT p.*, s.store_name, s.store_slug, c.category_name
          FROM products p
          JOIN stores s ON p.store_id = s.store_id
          JOIN categories c ON p.category_id = c.category_id
          WHERE {$whereString}
          ORDER BY {$orderBy}
          LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $products = $this->db->fetchAll($query, $params);
        
        // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM products p 
               JOIN stores s ON p.store_id = s.store_id 
               WHERE {$whereString}";
        unset($params['limit'], $params['offset']);
        $totalResult = $this->db->fetchOne($countQuery, $params);
        $total = $totalResult['total'];
        
        return [
            'products' => $products,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get product by slug
     * @param string $slug
     * @return array|false
     */
    public function getProductBySlug($slug) {
        $query = "SELECT p.*, s.store_name, s.store_slug, s.user_id as seller_id, 
                  c.category_name, u.full_name as seller_name
                  FROM products p
                  JOIN stores s ON p.store_id = s.store_id
                  JOIN categories c ON p.category_id = c.category_id
                  JOIN users u ON s.user_id = u.user_id
                  WHERE p.product_slug = :slug AND p.is_active = 1";
        
        $product = $this->db->fetchOne($query, ['slug' => $slug]);
        
        if ($product) {
            // Increment views
            $this->db->query("UPDATE products SET views_count = views_count + 1 WHERE product_id = :id", 
                ['id' => $product['product_id']]);
        }
        
        return $product;
    }
    
    /**
     * Get product by ID
     * @param int $productId
     * @return array|false
     */
    public function getProductById($productId) {
        $query = "SELECT p.*, s.store_name, s.store_slug, c.category_name 
                  FROM products p
                  JOIN stores s ON p.store_id = s.store_id
                  JOIN categories c ON p.category_id = c.category_id
                  WHERE p.product_id = :product_id";
        return $this->db->fetchOne($query, ['product_id' => $productId]);
    }
    
    /**
     * Create product (wrapper for createProduct)
     * @param array $data
     * @return array
     */
    public function create($data) {
        return $this->createProduct($data);
    }
    
    /**
     * Update product
     * @param int $productId
     * @param array $data
     * @return array
     */
    public function update($productId, $data) {
        try {
            $this->assertAdminWrite();
            
            // Update slug if name changed
            if (isset($data['product_name'])) {
                $currentProduct = $this->getProductById($productId);
                if ($currentProduct && $currentProduct['product_name'] !== $data['product_name']) {
                    $slug = $this->generateSlug($data['product_name']);
                    $counter = 1;
                    $originalSlug = $slug;
                    
                    // Check if slug exists (excluding current product)
                    while ($this->db->fetchOne("SELECT product_id FROM products WHERE product_slug = :slug AND product_id != :id", 
                        ['slug' => $slug, 'id' => $productId])) {
                        $slug = $originalSlug . '-' . $counter;
                        $counter++;
                    }
                    
                    $data['product_slug'] = $slug;
                }
            }
            
            $this->db->update('products', $data, 'product_id = :product_id', ['product_id' => $productId]);
            
            return ['success' => true, 'message' => 'Product updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Add product file
     * @param int $productId
     * @param array $fileData
     * @return array
     */
    public function addProductFile($productId, $fileData) {
        try {
            $insertData = [
                'product_id' => $productId,
                'file_name' => $fileData['file_name'],
                'file_original_name' => $fileData['file_original_name'],
                'file_path' => $fileData['file_path'],
                'file_size_bytes' => $fileData['file_size_bytes'],
                'file_type' => $fileData['file_type'],
                'file_extension' => $fileData['file_extension']
            ];
            
            $fileId = $this->db->insert('product_files', $insertData);
            
            // Update product file count
            $this->db->query("UPDATE products SET total_files = total_files + 1 WHERE product_id = :id",
                ['id' => $productId]);
            
            return ['success' => true, 'file_id' => $fileId];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get product files
     * @param int $productId
     * @return array
     */
    public function getProductFiles($productId) {
        $query = "SELECT * FROM product_files WHERE product_id = :product_id ORDER BY upload_date ASC";
        return $this->db->fetchAll($query, ['product_id' => $productId]);
    }
    
    /**
     * Generate slug from name
     * @param string $name
     * @return string
     */
    public function generateSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, 200);
    }
    
    /**
     * Check if slug exists
     * @param string $slug
     * @return bool
     */
    private function slugExists($slug) {
        return $this->db->exists('products', 'product_slug = :slug', ['slug' => $slug]);
    }
    
    /**
     * Approve product (Admin)
     * @param int $productId
     * @return array
     */
    public function approveProduct($productId) {
        try {
            $this->db->update('products', [
                'is_approved' => 1,
                'approval_status' => 'approved'
            ], 'product_id = :product_id', ['product_id' => $productId]);
            
            return ['success' => true, 'message' => 'Product approved'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Toggle featured status (Admin)
     * @param int $productId
     * @return array
     */
    public function toggleFeatured($productId) {
        try {
            $this->db->query("UPDATE products SET is_featured = NOT is_featured WHERE product_id = :id",
                ['id' => $productId]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

?>
