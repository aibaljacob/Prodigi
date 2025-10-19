<?php
/**
 * User Class - Handles all user-related operations
 * Implements OOP principles with proper encapsulation
 */

class User {
    private $db;
    private $userId;
    private $username;
    private $email;
    private $fullName;
    private $userType;
    private $profileImage;
    private $isVerified;
    private $isActive;
    private $createdAt;
    
    /**
     * Constructor
     * @param int|null $userId Optional user ID to load
     */
    public function __construct($userId = null) {
        $this->db = Database::getInstance();
        
        if ($userId) {
            $this->loadUser($userId);
        }
    }
    
    /**
     * Load user data by ID
     * @param int $userId
     * @return bool
     */
    private function loadUser($userId) {
        $query = "SELECT * FROM users WHERE user_id = :user_id";
        $user = $this->db->fetchOne($query, ['user_id' => $userId]);
        
        if ($user) {
            $this->userId = $user['user_id'];
            $this->username = $user['username'];
            $this->email = $user['email'];
            $this->fullName = $user['full_name'];
            $this->userType = $user['user_type'];
            $this->profileImage = $user['profile_image'];
            $this->isVerified = $user['is_verified'];
            $this->isActive = $user['is_active'];
            $this->createdAt = $user['created_at'];
            return true;
        }
        
        return false;
    }
    
    /**
     * Register new user
     * @param array $data
     * @return array ['success' => bool, 'message' => string, 'user_id' => int]
     */
    public function register($data) {
        try {
            // Validate input
            $validation = $this->validateRegistration($data);
            if (!$validation['success']) {
                return $validation;
            }
            
            // Check if username exists
            if ($this->usernameExists($data['username'])) {
                return ['success' => false, 'message' => 'Username already exists'];
            }
            
            // Check if email exists
            if ($this->emailExists($data['email'])) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
            
            // Hash password
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            
            // Generate verification token
            $verificationToken = bin2hex(random_bytes(32));
            
            // Insert user
            $insertData = [
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => $passwordHash,
                'full_name' => $data['full_name'],
                'phone' => $data['phone'] ?? null,
                'user_type' => 'buyer',
                'verification_token' => $verificationToken,
                'email_verified' => REQUIRE_EMAIL_VERIFICATION ? 0 : 1
            ];
            
            $userId = $this->db->insert('users', $insertData);
            
            // Log activity
            $this->logActivity($userId, 'user_registered', 'New user registered');
            
            // TODO: Send verification email
            
            return [
                'success' => true,
                'message' => 'Registration successful! Please verify your email.',
                'user_id' => $userId
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user
     * @param string $usernameOrEmail
     * @param string $password
     * @return array ['success' => bool, 'message' => string, 'user' => array]
     */
    public function login($usernameOrEmail, $password) {
        try {
            // Find user by username or email
            // Note: With ATTR_EMULATE_PREPARES=false, MySQL doesn't allow reusing the same named placeholder twice
            // Use distinct placeholders and bind the value to both to avoid HY093 errors
            $query = "SELECT * FROM users WHERE (username = :identifier_user OR email = :identifier_email) AND is_active = 1";
            $user = $this->db->fetchOne($query, [
                'identifier_user' => $usernameOrEmail,
                'identifier_email' => $usernameOrEmail,
            ]);
            
            if (!$user) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Verify password
            if (!password_verify($password, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Invalid credentials'];
            }
            
            // Check if email is verified (skip for admin)
            if (REQUIRE_EMAIL_VERIFICATION && $user['user_type'] !== 'admin' && !$user['email_verified']) {
                return ['success' => false, 'message' => 'Please verify your email first'];
            }
            
            // Update last login
            $this->db->update('users', 
                ['last_login' => date('Y-m-d H:i:s')],
                'user_id = :user_id',
                ['user_id' => $user['user_id']]
            );
            
            // Set session
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['logged_in'] = true;
            
            // Log activity
            $this->logActivity($user['user_id'], 'user_login', 'User logged in');
            
            return [
                'success' => true,
                'message' => 'Login successful',
                'user' => [
                    'user_id' => $user['user_id'],
                    'username' => $user['username'],
                    'email' => $user['email'],
                    'full_name' => $user['full_name'],
                    'user_type' => $user['user_type']
                ]
            ];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Logout user
     */
    public static function logout() {
        session_unset();
        session_destroy();
        header('Location: ' . APP_URL . '/login.php');
        exit();
    }
    
    /**
     * Check if user is logged in
     * @return bool
     */
    public static function isLoggedIn() {
        return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
    }
    
    /**
     * Check if current user is admin
     * @return bool
     */
    public static function isAdmin() {
        return self::isLoggedIn() && $_SESSION['user_type'] === 'admin';
    }
    
    /**
     * Check if current user is seller
     * @return bool
     */
    public static function isSeller() {
        // In single-vendor mode, there are no sellers besides admin
        if (defined('SINGLE_VENDOR') && SINGLE_VENDOR) {
            return false;
        }
        return self::isLoggedIn() && $_SESSION['user_type'] === 'seller';
    }

    /**
     * Check if current user is a normal user (not admin)
     * @return bool
     */
    public static function isUser() {
        return self::isLoggedIn() && !self::isAdmin();
    }
    
    /**
     * Get user data by ID
     * @param int $userId
     * @return array|null
     */
    public function getUserById($userId) {
        $query = "SELECT * FROM users WHERE user_id = :user_id";
        return $this->db->fetchOne($query, ['user_id' => $userId]);
    }
    
    /**
     * Get current user ID
     * @return int|null
     */
    public static function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
    
    /**
     * Update user profile
     * @param int $userId
     * @param array $data
     * @return array
     */
    public function updateProfile($userId, $data) {
        try {
            $updateData = [];
            
            if (isset($data['full_name'])) {
                $updateData['full_name'] = $data['full_name'];
            }
            
            if (isset($data['phone'])) {
                $updateData['phone'] = $data['phone'];
            }
            
            if (isset($data['profile_image'])) {
                $updateData['profile_image'] = $data['profile_image'];
            }
            
            if (empty($updateData)) {
                return ['success' => false, 'message' => 'No data to update'];
            }
            
            $this->db->update('users', $updateData, 'user_id = :user_id', ['user_id' => $userId]);
            
            // Log activity
            $this->logActivity($userId, 'profile_updated', 'User updated profile');
            
            return ['success' => true, 'message' => 'Profile updated successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Update failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Change password
     * @param int $userId
     * @param string $oldPassword
     * @param string $newPassword
     * @return array
     */
    public function changePassword($userId, $oldPassword, $newPassword) {
        try {
            // Get current password hash
            $query = "SELECT password_hash FROM users WHERE user_id = :user_id";
            $user = $this->db->fetchOne($query, ['user_id' => $userId]);
            
            if (!$user) {
                return ['success' => false, 'message' => 'User not found'];
            }
            
            // Verify old password
            if (!password_verify($oldPassword, $user['password_hash'])) {
                return ['success' => false, 'message' => 'Current password is incorrect'];
            }
            
            // Validate new password
            if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
                return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
            }
            
            // Update password
            $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $this->db->update('users',
                ['password_hash' => $newPasswordHash],
                'user_id = :user_id',
                ['user_id' => $userId]
            );
            
            // Log activity
            $this->logActivity($userId, 'password_changed', 'User changed password');
            
            return ['success' => true, 'message' => 'Password changed successfully'];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Password change failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Request seller account
     * @param int $userId
     * @return array
     */
    public function requestSellerAccount($userId) {
        try {
            // Check if already a seller
            $query = "SELECT user_type FROM users WHERE user_id = :user_id";
            $user = $this->db->fetchOne($query, ['user_id' => $userId]);
            
            if ($user['user_type'] === 'seller') {
                return ['success' => false, 'message' => 'You are already a seller'];
            }
            
            // Update to seller (pending approval if required)
            $updateData = ['user_type' => 'seller'];
            
            if (REQUIRE_SELLER_APPROVAL) {
                $updateData['is_verified'] = 0;
            } else {
                $updateData['is_verified'] = 1;
            }
            
            $this->db->update('users', $updateData, 'user_id = :user_id', ['user_id' => $userId]);
            
            // Update session
            $_SESSION['user_type'] = 'seller';
            
            // Log activity
            $this->logActivity($userId, 'seller_request', 'User requested seller account');
            
            $message = REQUIRE_SELLER_APPROVAL 
                ? 'Seller request submitted! Admin will review your account.' 
                : 'You are now a seller!';
            
            return ['success' => true, 'message' => $message];
            
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Request failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Validate registration data
     * @param array $data
     * @return array
     */
    private function validateRegistration($data) {
        if (empty($data['username']) || empty($data['email']) || empty($data['password']) || empty($data['full_name'])) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
            return ['success' => false, 'message' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'];
        }
        
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $data['username'])) {
            return ['success' => false, 'message' => 'Username must be 3-20 characters (letters, numbers, underscore only)'];
        }
        
        return ['success' => true];
    }
    
    /**
     * Check if username exists
     * @param string $username
     * @return bool
     */
    private function usernameExists($username) {
        return $this->db->exists('users', 'username = :username', ['username' => $username]);
    }
    
    /**
     * Check if email exists
     * @param string $email
     * @return bool
     */
    private function emailExists($email) {
        return $this->db->exists('users', 'email = :email', ['email' => $email]);
    }
    
    /**
     * Log user activity
     * @param int $userId
     * @param string $actionType
     * @param string $description
     */
    private function logActivity($userId, $actionType, $description) {
        try {
            $this->db->insert('activity_logs', [
                'user_id' => $userId,
                'action_type' => $actionType,
                'action_description' => $description,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Silently fail - logging should not break the application
        }
    }
    
    // Getters
    public function getUserId() { return $this->userId; }
    public function getUsername() { return $this->username; }
    public function getEmail() { return $this->email; }
    public function getFullName() { return $this->fullName; }
    public function getUserType() { return $this->userType; }
    public function getProfileImage() { return $this->profileImage; }
    public function isVerified() { return $this->isVerified; }
    public function isActive() { return $this->isActive; }
    public function getCreatedAt() { return $this->createdAt; }
}

?>
