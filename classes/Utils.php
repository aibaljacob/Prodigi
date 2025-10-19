<?php
/**
 * FileUpload Class - Handles file uploads with security
 */

class FileUpload {
    private $uploadDir;
    private $allowedTypes;
    private $maxSize;
    
    public function __construct($uploadDir, $allowedTypes, $maxSize = MAX_FILE_SIZE) {
        $this->uploadDir = $uploadDir;
        $this->allowedTypes = $allowedTypes;
        $this->maxSize = $maxSize;
        
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    }
    
    /**
     * Upload file
     */
    public function upload($file) {
        try {
            // Validate file
            $validation = $this->validateFile($file);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Generate unique filename
            $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $newFileName = uniqid() . '_' . time() . '.' . $extension;
            $targetPath = $this->uploadDir . '/' . $newFileName;
            
            // Move uploaded file
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return [
                    'success' => true,
                    'file_name' => $newFileName,
                    'file_original_name' => $file['name'],
                    'file_path' => $targetPath,
                    'file_size_bytes' => $file['size'],
                    'file_type' => $file['type'],
                    'file_extension' => $extension
                ];
            } else {
                return ['success' => false, 'message' => 'Failed to move uploaded file'];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Upload multiple files
     */
    public function uploadMultiple($files) {
        $results = [];
        $fileCount = count($files['name']);
        
        for ($i = 0; $i < $fileCount; $i++) {
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];
            
            $result = $this->upload($file);
            $results[] = $result;
        }
        
        return $results;
    }
    
    /**
     * Validate file
     */
    private function validateFile($file) {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'message' => 'File upload error: ' . $file['error']];
        }
        
        // Check file size
        if ($file['size'] > $this->maxSize) {
            $maxSizeMB = $this->maxSize / (1024 * 1024);
            return ['success' => false, 'message' => "File size exceeds {$maxSizeMB}MB limit"];
        }
        
        // Check file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $this->allowedTypes)) {
            return ['success' => false, 'message' => 'File type not allowed'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Delete file
     */
    public static function delete($filePath) {
        if (file_exists($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Get file size in human-readable format
     */
    public static function formatFileSize($bytes) {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, 2) . ' ' . $units[$pow];
    }
}

/**
 * Utility Class - Helper functions
 */

class Utils {
    /**
     * Sanitize input
     */
    public static function sanitize($data) {
        if (is_array($data)) {
            return array_map([self::class, 'sanitize'], $data);
        }
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRF($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF token
     */
    public static function getCSRFToken() {
        return $_SESSION['csrf_token'] ?? '';
    }
    
    /**
     * Redirect
     */
    public static function redirect($url, $message = null, $type = 'info') {
        if ($message) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        header("Location: $url");
        exit();
    }
    
    /**
     * Set flash message
     */
    public static function setFlashMessage($message, $type = 'info') {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    /**
     * Get flash message
     */
    public static function getFlashMessage() {
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_type'] ?? 'info';
            unset($_SESSION['flash_message'], $_SESSION['flash_type']);
            return ['message' => $message, 'type' => $type];
        }
        return null;
    }
    
    /**
     * Display flash message
     */
    public static function displayFlashMessage() {
        $flash = self::getFlashMessage();
        if ($flash) {
            $type = $flash['type'];
            $message = htmlspecialchars($flash['message']);
            $bgColor = $type === 'success' ? 'var(--neon-green)' : ($type === 'error' ? '#ef4444' : 'var(--neon-cyan)');
            echo '<div class="flash-message" style="background: ' . $bgColor . '; color: #000; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 500;">' . $message . '</div>';
        }
    }
    
    /**
     * Format currency
     */
    public static function formatCurrency($amount) {
        if ($amount === null || $amount === '') {
            $amount = 0;
        }
        return '₹' . number_format((float)$amount, 2);
    }
    
    /**
     * Time ago
     */
    public static function timeAgo($datetime) {
        $timestamp = strtotime($datetime);
        $difference = time() - $timestamp;
        
        $periods = [
            'year' => 31536000,
            'month' => 2592000,
            'week' => 604800,
            'day' => 86400,
            'hour' => 3600,
            'minute' => 60,
            'second' => 1
        ];
        
        foreach ($periods as $key => $value) {
            if ($difference >= $value) {
                $time = floor($difference / $value);
                return $time . ' ' . $key . ($time > 1 ? 's' : '') . ' ago';
            }
        }
        
        return 'just now';
    }
    
    /**
     * Truncate text
     */
    public static function truncate($text, $length = 100, $suffix = '...') {
        if (strlen($text) > $length) {
            return substr($text, 0, $length) . $suffix;
        }
        return $text;
    }
    
    /**
     * Generate random string
     */
    public static function generateRandomString($length = 10) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Validate email
     */
    public static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Get client IP
     */
    public static function getClientIP() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }
    
    /**
     * JSON response
     */
    public static function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}

/**
 * Category Class
 */
class Category {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function getAllCategories($activeOnly = false) {
        $activeFilter = $activeOnly ? "WHERE c.is_active = 1" : "";
        $query = "SELECT c.*, 
                  (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id AND p.is_active = 1 AND p.is_approved = 1) as product_count,
                  parent.category_name as parent_name
                  FROM categories c
                  LEFT JOIN categories parent ON c.parent_category_id = parent.category_id
                  {$activeFilter}
                  ORDER BY c.display_order ASC, c.category_name ASC";
        return $this->db->fetchAll($query);
    }
    
    public function getCategoryById($categoryId) {
        return $this->db->fetchOne("SELECT * FROM categories WHERE category_id = :id", ['id' => $categoryId]);
    }
    
    public function getCategoryBySlug($slug) {
        return $this->db->fetchOne("SELECT * FROM categories WHERE category_slug = :slug", ['slug' => $slug]);
    }
    
    public function createCategory($data) {
        try {
            // Generate slug
            $slug = $this->generateSlug($data['category_name']);
            
            // Check if slug exists
            if ($this->getCategoryBySlug($slug)) {
                $slug .= '-' . time();
            }
            
            $insertData = [
                'category_name' => $data['category_name'],
                'category_slug' => $slug,
                'category_description' => $data['category_description'] ?? '',
                'parent_category_id' => !empty($data['parent_category_id']) ? $data['parent_category_id'] : null,
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => isset($data['is_active']) ? 1 : 0
            ];
            
            $this->db->insert('categories', $insertData);
            return ['success' => true, 'message' => 'Category created successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function updateCategory($categoryId, $data) {
        try {
            // Generate slug if name changed
            $category = $this->getCategoryById($categoryId);
            if ($category['category_name'] !== $data['category_name']) {
                $slug = $this->generateSlug($data['category_name']);
                
                // Check if slug exists (excluding current category)
                $existing = $this->getCategoryBySlug($slug);
                if ($existing && $existing['category_id'] != $categoryId) {
                    $slug .= '-' . time();
                }
            } else {
                $slug = $category['category_slug'];
            }
            
            $updateData = [
                'category_name' => $data['category_name'],
                'category_slug' => $slug,
                'category_description' => $data['category_description'] ?? '',
                'parent_category_id' => !empty($data['parent_category_id']) ? $data['parent_category_id'] : null,
                'display_order' => $data['display_order'] ?? 0,
                'is_active' => isset($data['is_active']) ? 1 : 0
            ];
            
            $this->db->update('categories', $updateData, 'category_id = :id', ['id' => $categoryId]);
            return ['success' => true, 'message' => 'Category updated successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function deleteCategory($categoryId) {
        try {
            // Check if category has products
            $query = "SELECT COUNT(*) as count FROM products WHERE category_id = :id AND is_active = 1";
            $result = $this->db->fetchOne($query, ['id' => $categoryId]);
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete category with active products'];
            }
            
            // Check if category has subcategories
            $query = "SELECT COUNT(*) as count FROM categories WHERE parent_category_id = :id";
            $result = $this->db->fetchOne($query, ['id' => $categoryId]);
            
            if ($result['count'] > 0) {
                return ['success' => false, 'message' => 'Cannot delete category with subcategories'];
            }
            
            $this->db->delete('categories', 'category_id = :id', ['id' => $categoryId]);
            return ['success' => true, 'message' => 'Category deleted successfully'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    private function generateSlug($text) {
        // Convert to lowercase
        $text = strtolower($text);
        // Replace spaces with hyphens
        $text = preg_replace('/\s+/', '-', $text);
        // Remove special characters
        $text = preg_replace('/[^a-z0-9\-]/', '', $text);
        // Remove multiple hyphens
        $text = preg_replace('/-+/', '-', $text);
        // Trim hyphens from ends
        $text = trim($text, '-');
        return $text;
    }
}

/**
 * Review Class
 */
class Review {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function addReview($data) {
        try {
            // Check if user already reviewed
            if ($this->db->exists('reviews', 'transaction_id = :tid', ['tid' => $data['transaction_id']])) {
                return ['success' => false, 'message' => 'You already reviewed this product'];
            }
            
            $this->db->insert('reviews', [
                'product_id' => $data['product_id'],
                'user_id' => $data['user_id'],
                'transaction_id' => $data['transaction_id'],
                'rating' => $data['rating'],
                'review_title' => $data['review_title'] ?? '',
                'review_text' => $data['review_text'] ?? ''
            ]);
            
            return ['success' => true, 'message' => 'Review submitted!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    public function getProductReviews($productId, $limit = 10) {
        $query = "SELECT r.*, u.username, u.full_name, u.profile_image
                  FROM reviews r
                  JOIN users u ON r.user_id = u.user_id
                  WHERE r.product_id = :pid AND r.is_approved = 1
                  ORDER BY r.created_at DESC
                  LIMIT :limit";
        
        return $this->db->fetchAll($query, ['pid' => $productId, 'limit' => $limit]);
    }
}

?>
