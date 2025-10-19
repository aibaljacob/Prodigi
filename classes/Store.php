<?php
/**
 * Store Class - Handles seller store operations
 * Implements OOP principles
 */

class Store {
    private $db;
    private $storeId;
    private $userId;
    private $storeName;
    private $storeSlug;
    private $storeDescription;
    private $storeLogo;
    private $storeBanner;
    private $isApproved;
    private $approvalStatus;
    
    /**
     * Constructor
     * @param int|null $storeId
     */
    public function __construct($storeId = null) {
        $this->db = Database::getInstance();
        
        if ($storeId) {
            $this->loadStore($storeId);
        }
    }
    
    /**
     * Load store data
     * @param int $storeId
     * @return bool
     */
    private function loadStore($storeId) {
        $query = "SELECT * FROM stores WHERE store_id = :store_id";
        $store = $this->db->fetchOne($query, ['store_id' => $storeId]);
        
        if ($store) {
            $this->storeId = $store['store_id'];
            $this->userId = $store['user_id'];
            $this->storeName = $store['store_name'];
            $this->storeSlug = $store['store_slug'];
            $this->storeDescription = $store['store_description'];
            $this->storeLogo = $store['store_logo'];
            $this->storeBanner = $store['store_banner'];
            $this->isApproved = $store['is_approved'];
            $this->approvalStatus = $store['approval_status'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Create new store
     * @param array $data
     * @return array
     */
    public function createStore($data) {
        try {
            // Validate input
            if (empty($data['user_id']) || empty($data['store_name'])) {
                return ['success' => false, 'message' => 'Store name is required'];
            }
            
            // Check if user already has a store
            if ($this->userHasStore($data['user_id'])) {
                return ['success' => false, 'message' => 'You already have a store'];
            }
            
            // Generate unique slug
            $slug = $this->generateSlug($data['store_name']);
            
            // Check if slug exists
            $counter = 1;
            $originalSlug = $slug;
            while ($this->slugExists($slug)) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }
            
            // Set approval status
            $approvalStatus = REQUIRE_SELLER_APPROVAL ? 'pending' : 'approved';
            $isApproved = REQUIRE_SELLER_APPROVAL ? 0 : 1;
            
            // Insert store
            $insertData = [
                'user_id' => $data['user_id'],
                'store_name' => $data['store_name'],
                'store_slug' => $slug,
                'store_description' => $data['store_description'] ?? '',
                'approval_status' => $approvalStatus,
                'is_approved' => $isApproved
            ];
            
            $storeId = $this->db->insert('stores', $insertData);
            
            $message = REQUIRE_SELLER_APPROVAL 
                ? 'Store created! Waiting for admin approval.' 
                : 'Store created successfully!';
            
            return [
                'success' => true,
                'message' => $message,
                'store_id' => $storeId,
                'store_slug' => $slug
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Store creation failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update store
     * @param int $storeId
     * @param array $data
     * @return array
     */
    public function updateStore($storeId, $data) {
        try {
            $updateData = [];
            
            if (isset($data['store_name'])) {
                $updateData['store_name'] = $data['store_name'];
            }
            
            if (isset($data['store_description'])) {
                $updateData['store_description'] = $data['store_description'];
            }
            
            if (isset($data['store_logo'])) {
                $updateData['store_logo'] = $data['store_logo'];
            }
            
            if (isset($data['store_banner'])) {
                $updateData['store_banner'] = $data['store_banner'];
            }
            
            if (isset($data['social_links'])) {
                $updateData['social_links'] = json_encode($data['social_links']);
            }
            
            if (empty($updateData)) {
                return ['success' => false, 'message' => 'No data to update'];
            }
            
            $this->db->update('stores', $updateData, 'store_id = :store_id', ['store_id' => $storeId]);
            
            return ['success' => true, 'message' => 'Store updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Get store by user ID
     * @param int $userId
     * @return array|false
     */
    public function getStoreByUserId($userId) {
        $query = "SELECT * FROM stores WHERE user_id = :user_id";
        return $this->db->fetchOne($query, ['user_id' => $userId]);
    }
    
    /**
     * Get store by slug
     * @param string $slug
     * @return array|false
     */
    public function getStoreBySlug($slug) {
        $query = "SELECT s.*, u.username, u.email, u.full_name 
                  FROM stores s 
                  JOIN users u ON s.user_id = u.user_id 
                  WHERE s.store_slug = :slug AND s.is_active = 1";
        return $this->db->fetchOne($query, ['slug' => $slug]);
    }
    
    /**
     * Get store analytics
     * @param int $storeId
     * @return array
     */
    public function getStoreAnalytics($storeId) {
        $query = "SELECT 
                    COUNT(DISTINCT p.product_id) as total_products,
                    COUNT(DISTINCT t.transaction_id) as total_sales,
                    COALESCE(SUM(t.seller_earnings), 0) as total_earnings,
                    COALESCE(AVG(r.rating), 0) as average_rating,
                    COUNT(DISTINCT r.review_id) as total_reviews
                  FROM stores s
                  LEFT JOIN products p ON s.store_id = p.store_id AND p.is_active = 1
                  LEFT JOIN transactions t ON p.product_id = t.product_id AND t.payment_status = 'completed'
                  LEFT JOIN reviews r ON p.product_id = r.product_id AND r.is_approved = 1
                  WHERE s.store_id = :store_id";
        
        return $this->db->fetchOne($query, ['store_id' => $storeId]);
    }
    
    /**
     * Get all stores with filters
     * @param array $filters
     * @param int $page
     * @param int $limit
     * @return array
     */
    public function getAllStores($filters = [], $page = 1, $limit = 12) {
        $offset = ($page - 1) * $limit;
        $where = ['1=1'];
        $params = [];
        
        if (isset($filters['approval_status'])) {
            $where[] = 's.approval_status = :approval_status';
            $params['approval_status'] = $filters['approval_status'];
        }
        
        if (isset($filters['is_active'])) {
            $where[] = 's.is_active = :is_active';
            $params['is_active'] = $filters['is_active'];
        }
        
        if (isset($filters['search'])) {
            $where[] = '(s.store_name LIKE :search OR s.store_description LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $whereString = implode(' AND ', $where);
        
        $query = "SELECT s.*, u.username, u.full_name,
                  COUNT(DISTINCT p.product_id) as product_count
                  FROM stores s
                  JOIN users u ON s.user_id = u.user_id
                  LEFT JOIN products p ON s.store_id = p.store_id AND p.is_active = 1
                  WHERE {$whereString}
                  GROUP BY s.store_id
                  ORDER BY s.created_at DESC
                  LIMIT :limit OFFSET :offset";
        
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        $stores = $this->db->fetchAll($query, $params);
        
        // Get total count
        $countQuery = "SELECT COUNT(DISTINCT s.store_id) as total FROM stores s WHERE {$whereString}";
        unset($params['limit'], $params['offset']);
        $totalResult = $this->db->fetchOne($countQuery, $params);
        $total = $totalResult['total'];
        
        return [
            'stores' => $stores,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Check if user has a store
     * @param int $userId
     * @return bool
     */
    private function userHasStore($userId) {
        return $this->db->exists('stores', 'user_id = :user_id', ['user_id' => $userId]);
    }
    
    /**
     * Check if slug exists
     * @param string $slug
     * @return bool
     */
    private function slugExists($slug) {
        return $this->db->exists('stores', 'store_slug = :slug', ['slug' => $slug]);
    }
    
    /**
     * Generate slug from name
     * @param string $name
     * @return string
     */
    private function generateSlug($name) {
        $slug = strtolower(trim($name));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return substr($slug, 0, 100);
    }
    
    /**
     * Approve store (Admin function)
     * @param int $storeId
     * @return array
     */
    public function approveStore($storeId) {
        try {
            $this->db->update('stores', [
                'is_approved' => 1,
                'approval_status' => 'approved',
                'approval_date' => date('Y-m-d H:i:s')
            ], 'store_id = :store_id', ['store_id' => $storeId]);
            
            // Send notification to seller
            $store = $this->loadStore($storeId);
            $this->sendNotification($this->userId, 'Store Approved', 'Your store has been approved!');
            
            return ['success' => true, 'message' => 'Store approved successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Approval failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reject store (Admin function)
     * @param int $storeId
     * @return array
     */
    public function rejectStore($storeId) {
        try {
            $this->db->update('stores', [
                'is_approved' => 0,
                'approval_status' => 'rejected'
            ], 'store_id = :store_id', ['store_id' => $storeId]);
            
            return ['success' => true, 'message' => 'Store rejected'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Rejection failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Send notification to user
     * @param int $userId
     * @param string $title
     * @param string $message
     */
    private function sendNotification($userId, $title, $message) {
        try {
            $this->db->insert('notifications', [
                'user_id' => $userId,
                'notification_type' => 'store',
                'title' => $title,
                'message' => $message
            ]);
        } catch (Exception $e) {
            // Silently fail
        }
    }
    
    // Getters
    public function getStoreId() { return $this->storeId; }
    public function getUserId() { return $this->userId; }
    public function getStoreName() { return $this->storeName; }
    public function getStoreSlug() { return $this->storeSlug; }
    public function getStoreDescription() { return $this->storeDescription; }
    public function getStoreLogo() { return $this->storeLogo; }
    public function getStoreBanner() { return $this->storeBanner; }
    public function isApproved() { return $this->isApproved; }
    public function getApprovalStatus() { return $this->approvalStatus; }
}

?>
