<?php
/**
 * Transaction Class - Payment and order handling
 */

class Transaction {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Create transaction
     */
    public function createTransaction($data) {
        try {
            $this->db->beginTransaction();
            
            $product = $this->db->fetchOne("SELECT * FROM products WHERE product_id = :id", 
                ['id' => $data['product_id']]);
            
            if (!$product) {
                throw new Exception("Product not found");
            }
            
            $amount = $product['discount_price'] ?? $product['price'];
            $commissionPercentage = $data['commission_percentage'] ?? DEFAULT_COMMISSION_PERCENTAGE;
            $commissionAmount = ($amount * $commissionPercentage) / 100;
            $sellerEarnings = $amount - $commissionAmount;
            
            // Get seller ID from store
            $store = $this->db->fetchOne("SELECT user_id FROM stores WHERE store_id = :id",
                ['id' => $product['store_id']]);
            
            $insertData = [
                'transaction_uuid' => $this->generateUUID(),
                'buyer_id' => $data['buyer_id'],
                'seller_id' => $store['user_id'],
                'product_id' => $data['product_id'],
                'amount' => $amount,
                'commission_percentage' => $commissionPercentage,
                'commission_amount' => $commissionAmount,
                'seller_earnings' => $sellerEarnings,
                'payment_gateway' => 'razorpay',
                'payment_status' => 'pending',
                'download_token' => bin2hex(random_bytes(32)),
                'download_expiry' => date('Y-m-d H:i:s', strtotime('+' . DEFAULT_DOWNLOAD_EXPIRY_HOURS . ' hours'))
            ];
            
            $transactionId = $this->db->insert('transactions', $insertData);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'transaction_uuid' => $insertData['transaction_uuid']
            ];
            
        } catch (Exception $e) {
            $this->db->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($transactionId, $status, $paymentId = null) {
        try {
            $updateData = [
                'payment_status' => $status,
                'payment_date' => date('Y-m-d H:i:s')
            ];
            
            if ($paymentId) {
                $updateData['payment_id'] = $paymentId;
            }
            
            $this->db->update('transactions', $updateData, 
                'transaction_id = :id', ['id' => $transactionId]);
            
            // If completed, remove from cart
            if ($status === 'completed') {
                $transaction = $this->db->fetchOne(
                    "SELECT buyer_id, product_id FROM transactions WHERE transaction_id = :id",
                    ['id' => $transactionId]
                );
                
                $this->db->delete('shopping_cart', 
                    'user_id = :uid AND product_id = :pid',
                    ['uid' => $transaction['buyer_id'], 'pid' => $transaction['product_id']]
                );
            }
            
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get user purchases
     */
    public function getUserPurchases($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT t.*, p.product_name, p.thumbnail_image, s.store_name, s.store_slug
                  FROM transactions t
                  JOIN products p ON t.product_id = p.product_id
                  JOIN stores s ON p.store_id = s.store_id
                  WHERE t.buyer_id = :user_id AND t.payment_status = 'completed'
                  ORDER BY t.transaction_date DESC
                  LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($query, [
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    /**
     * Get seller sales
     */
    public function getSellerSales($userId, $page = 1, $limit = 10) {
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT t.*, p.product_name, u.username as buyer_username
                  FROM transactions t
                  JOIN products p ON t.product_id = p.product_id
                  JOIN users u ON t.buyer_id = u.user_id
                  WHERE t.seller_id = :user_id AND t.payment_status = 'completed'
                  ORDER BY t.transaction_date DESC
                  LIMIT :limit OFFSET :offset";
        
        return $this->db->fetchAll($query, [
            'user_id' => $userId,
            'limit' => $limit,
            'offset' => $offset
        ]);
    }
    
    /**
     * Get seller earnings
     */
    public function getSellerEarnings($userId) {
        $query = "SELECT 
                    COALESCE(SUM(seller_earnings), 0) as total_earnings,
                    COUNT(*) as total_sales
                  FROM transactions 
                  WHERE seller_id = :user_id AND payment_status = 'completed'";
        
        return $this->db->fetchOne($query, ['user_id' => $userId]);
    }
    
    /**
     * Generate UUID
     */
    private function generateUUID() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
    
    /**
     * Record download
     */
    public function recordDownload($transactionId) {
        try {
            $this->db->query(
                "UPDATE transactions 
                 SET download_count = download_count + 1, is_downloaded = 1 
                 WHERE transaction_id = :id",
                ['id' => $transactionId]
            );
            return ['success' => true];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Validate download
     */
    public function validateDownload($downloadToken) {
        $query = "SELECT t.*, p.product_name, pf.file_path, pf.file_original_name
                  FROM transactions t
                  JOIN products p ON t.product_id = p.product_id
                  JOIN product_files pf ON p.product_id = pf.product_id
                  WHERE t.download_token = :token 
                    AND t.payment_status = 'completed'
                    AND t.download_expiry > NOW()
                    AND t.download_count < :limit";
        
        return $this->db->fetchAll($query, [
            'token' => $downloadToken,
            'limit' => DEFAULT_DOWNLOAD_LIMIT
        ]);
    }
}

?>
