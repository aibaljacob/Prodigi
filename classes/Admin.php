<?php
/**
 * Admin Class - Admin panel operations
 */

class Admin {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Get dashboard statistics
     */
    public function getDashboardStats() {
        $query = "SELECT * FROM view_admin_stats";
        return $this->db->fetchOne($query);
    }
    
    /**
     * Get all users
     */
    public function getAllUsers($filters = [], $page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        $where = ['user_type != "admin"'];
        $params = [];
        
        if (isset($filters['user_type'])) {
            $where[] = 'user_type = :user_type';
            $params['user_type'] = $filters['user_type'];
        }
        
        if (isset($filters['status'])) {
            if ($filters['status'] === 'active') {
                $where[] = 'is_active = 1';
            } elseif ($filters['status'] === 'inactive') {
                $where[] = 'is_active = 0';
            }
        }
        
        if (isset($filters['search'])) {
            $where[] = '(username LIKE :search OR email LIKE :search OR full_name LIKE :search)';
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $whereString = implode(' AND ', $where);
        
        $query = "SELECT * FROM users WHERE {$whereString} 
                  ORDER BY created_at DESC LIMIT :limit OFFSET :offset";
        $params['limit'] = $limit;
        $params['offset'] = $offset;
        
        return $this->db->fetchAll($query, $params);
    }
    
    /**
     * Get pending store approvals
     */
    public function getPendingStoreApprovals() {
        $query = "SELECT s.*, u.username, u.email, u.full_name
                  FROM stores s
                  JOIN users u ON s.user_id = u.user_id
                  WHERE s.approval_status = 'pending'
                  ORDER BY s.created_at DESC";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get pending product approvals
     */
    public function getPendingProductApprovals() {
        $query = "SELECT p.*, s.store_name, c.category_name
                  FROM products p
                  JOIN stores s ON p.store_id = s.store_id
                  JOIN categories c ON p.category_id = c.category_id
                  WHERE p.approval_status = 'pending'
                  ORDER BY p.created_at DESC";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Get all transactions
     */
    public function getAllTransactions($page = 1, $limit = 20) {
        $offset = ($page - 1) * $limit;
        
        if (defined('SINGLE_VENDOR') && SINGLE_VENDOR) {
            $query = "SELECT t.*, 
                        buyer.username as buyer_username,
                        p.product_name
                      FROM transactions t
                      JOIN users buyer ON t.buyer_id = buyer.user_id
                      JOIN products p ON t.product_id = p.product_id
                      ORDER BY t.transaction_date DESC
                      LIMIT :limit OFFSET :offset";
        } else {
            $query = "SELECT t.*, 
                        buyer.username as buyer_username,
                        seller.username as seller_username,
                        p.product_name
                      FROM transactions t
                      JOIN users buyer ON t.buyer_id = buyer.user_id
                      JOIN users seller ON t.seller_id = seller.user_id
                      JOIN products p ON t.product_id = p.product_id
                      ORDER BY t.transaction_date DESC
                      LIMIT :limit OFFSET :offset";
        }
        
        return $this->db->fetchAll($query, ['limit' => $limit, 'offset' => $offset]);
    }
    
    /**
     * Get pending payouts
     */
    public function getPendingPayouts() {
        $query = "SELECT p.*, u.username, u.email, u.full_name
                  FROM payouts p
                  JOIN users u ON p.seller_id = u.user_id
                  WHERE p.status = 'pending'
                  ORDER BY p.request_date DESC";
        return $this->db->fetchAll($query);
    }
    
    /**
     * Approve payout
     */
    public function approvePayout($payoutId, $adminId, $notes = '') {
        try {
            $this->db->update('payouts', [
                'status' => 'completed',
                'processed_date' => date('Y-m-d H:i:s'),
                'processed_by' => $adminId,
                'admin_notes' => $notes
            ], 'payout_id = :id', ['id' => $payoutId]);
            
            return ['success' => true, 'message' => 'Payout approved'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get/Update admin settings
     */
    public function getSetting($key) {
        $result = $this->db->fetchOne(
            "SELECT setting_value FROM admin_settings WHERE setting_key = :key",
            ['key' => $key]
        );
        return $result ? $result['setting_value'] : null;
    }
    
    public function updateSetting($key, $value, $adminId) {
        try {
            $exists = $this->db->exists('admin_settings', 'setting_key = :key', ['key' => $key]);
            
            if ($exists) {
                $this->db->update('admin_settings', [
                    'setting_value' => $value,
                    'updated_by' => $adminId
                ], 'setting_key = :key', ['key' => $key]);
            } else {
                $this->db->insert('admin_settings', [
                    'setting_key' => $key,
                    'setting_value' => $value,
                    'updated_by' => $adminId
                ]);
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get revenue stats
     */
    public function getRevenueStats($period = 'month') {
        $dateFormat = $period === 'day' ? '%Y-%m-%d' : '%Y-%m';
        
        $query = "SELECT 
                    DATE_FORMAT(transaction_date, :format) as period,
                    COUNT(*) as transactions,
                    SUM(amount) as revenue,
                    SUM(commission_amount) as commission
                  FROM transactions
                  WHERE payment_status = 'completed'
                    AND transaction_date >= DATE_SUB(NOW(), INTERVAL 12 {$period})
                  GROUP BY period
                  ORDER BY period ASC";
        
        return $this->db->fetchAll($query, ['format' => $dateFormat]);
    }
    
    /**
     * Ban/Unban user
     */
    public function toggleUserStatus($userId) {
        try {
            $this->db->query("UPDATE users SET is_active = NOT is_active WHERE user_id = :id",
                ['id' => $userId]);
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Delete product
     */
    public function deleteProduct($productId) {
        try {
            $this->db->update('products', ['is_active' => 0], 
                'product_id = :id', ['id' => $productId]);
            return ['success' => true, 'message' => 'Product deleted'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}

?>
