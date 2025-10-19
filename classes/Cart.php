<?php
/**
 * Cart Class - Shopping cart operations
 */

class Cart {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    /**
     * Add item to cart
     */
    public function addItem($userId, $productId) {
        try {
            // Check if already in cart
            if ($this->db->exists('shopping_cart', 'user_id = :uid AND product_id = :pid', 
                ['uid' => $userId, 'pid' => $productId])) {
                return ['success' => false, 'message' => 'Product already in cart'];
            }
            
            // Check if user already owns this product
            if ($this->userOwnsProduct($userId, $productId)) {
                return ['success' => false, 'message' => 'You already own this product'];
            }
            
            $this->db->insert('shopping_cart', [
                'user_id' => $userId,
                'product_id' => $productId
            ]);
            
            return ['success' => true, 'message' => 'Added to cart'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Get cart items
     */
    public function getCartItems($userId) {
        $query = "SELECT c.cart_id, p.*, s.store_name, s.store_slug, cat.category_name
                  FROM shopping_cart c
                  JOIN products p ON c.product_id = p.product_id
                  JOIN stores s ON p.store_id = s.store_id
                  JOIN categories cat ON p.category_id = cat.category_id
                  WHERE c.user_id = :user_id AND p.is_active = 1 AND p.is_approved = 1
                  ORDER BY c.added_at DESC";
        return $this->db->fetchAll($query, ['user_id' => $userId]);
    }
    
    /**
     * Get cart (alias for getCartItems)
     */
    public function getCart($userId) {
        return $this->getCartItems($userId);
    }
    
    /**
     * Get cart total
     */
    public function getCartTotal($userId) {
        $items = $this->getCartItems($userId);
        $total = 0;
        foreach ($items as $item) {
            $total += $item['discount_price'] ?? $item['price'];
        }
        return $total;
    }
    
    /**
     * Get cart count
     */
    public function getCartCount($userId) {
        return $this->db->count('shopping_cart', 'user_id = :user_id', ['user_id' => $userId]);
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($cartId, $userId) {
        try {
            $this->db->delete('shopping_cart', 'cart_id = :cid AND user_id = :uid', 
                ['cid' => $cartId, 'uid' => $userId]);
            return ['success' => true, 'message' => 'Removed from cart'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Clear cart
     */
    public function clearCart($userId) {
        $this->db->delete('shopping_cart', 'user_id = :user_id', ['user_id' => $userId]);
    }
    
    /**
     * Check if user owns product
     */
    private function userOwnsProduct($userId, $productId) {
        return $this->db->exists('transactions', 
            'buyer_id = :uid AND product_id = :pid AND payment_status = "completed"',
            ['uid' => $userId, 'pid' => $productId]);
    }
}

?>
