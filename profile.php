<?php
/**
 * User Profile Page
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Redirect if not logged in
if (!User::isLoggedIn()) {
    header('Location: ' . APP_URL . '/login.php');
    exit;
}

$userId = User::getCurrentUserId();
$user = new User($userId);
$userData = $user->getUserById($userId);

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'update_profile') {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            Utils::setFlashMessage('Invalid request', 'error');
        } else {
            $updateData = [
                'full_name' => trim($_POST['full_name']),
                'phone' => trim($_POST['phone']),
            ];
            
            $result = $user->updateProfile($userId, $updateData);
            if ($result['success']) {
                Utils::setFlashMessage('Profile updated successfully', 'success');
                $userData = $user->getUserById($userId); // Refresh data
            } else {
                Utils::setFlashMessage($result['message'], 'error');
            }
        }
        header('Location: ' . APP_URL . '/profile.php');
        exit;
    }
    
    if ($_POST['action'] === 'change_password') {
        // CSRF validation
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            Utils::setFlashMessage('Invalid request', 'error');
        } else {
            $currentPassword = $_POST['current_password'];
            $newPassword = $_POST['new_password'];
            $confirmPassword = $_POST['confirm_password'];
            
            if ($newPassword !== $confirmPassword) {
                Utils::setFlashMessage('New passwords do not match', 'error');
            } elseif (strlen($newPassword) < 6) {
                Utils::setFlashMessage('Password must be at least 6 characters', 'error');
            } else {
                $result = $user->changePassword($userId, $currentPassword, $newPassword);
                if ($result['success']) {
                    Utils::setFlashMessage('Password changed successfully', 'success');
                } else {
                    Utils::setFlashMessage($result['message'], 'error');
                }
            }
        }
        header('Location: ' . APP_URL . '/profile.php');
        exit;
    }
}

// Get user's purchases
$db = Database::getInstance();
$purchases = $db->fetchAll(
    "SELECT t.*, p.product_name, p.product_slug, p.thumbnail_image, p.product_file_path
     FROM transactions t
     JOIN products p ON t.product_id = p.product_id
     WHERE t.buyer_id = :user_id
     ORDER BY t.transaction_date DESC
     LIMIT 20",
    ['user_id' => $userId]
);

// Generate CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .profile-container {
            display: grid;
            grid-template-columns: 300px 1fr;
            gap: 2rem;
            padding: 3rem 1.5rem;
        }
        
        .profile-sidebar {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 2rem;
            height: fit-content;
            position: sticky;
            top: 100px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--neon-green), var(--accent-cyan));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--bg-primary);
            margin: 0 auto 1.5rem;
            border: 3px solid var(--border-color);
        }
        
        .profile-name {
            text-align: center;
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .profile-email {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
        
        .profile-nav {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .profile-nav-item {
            padding: 0.75rem 1rem;
            border-radius: var(--radius-sm);
            color: var(--text-secondary);
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .profile-nav-item:hover {
            background: rgba(57, 255, 20, 0.1);
            color: var(--neon-green);
        }
        
        .profile-nav-item.active {
            background: rgba(57, 255, 20, 0.1);
            color: var(--neon-green);
            border-left: 3px solid var(--neon-green);
        }
        
        .profile-content {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 2rem;
        }
        
        .profile-section {
            display: none;
        }
        
        .profile-section.active {
            display: block;
        }
        
        .profile-section h2 {
            font-size: 1.8rem;
            color: var(--text-primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--border-color);
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--text-primary);
            font-weight: 600;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            color: var(--text-primary);
            font-size: 1rem;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--neon-green);
            box-shadow: 0 0 0 3px rgba(57, 255, 20, 0.1);
        }
        
        .form-group input:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
        
        .purchase-item {
            display: grid;
            grid-template-columns: 100px 1fr auto;
            gap: 1.5rem;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .purchase-item:hover {
            border-color: var(--neon-green);
            box-shadow: 0 0 20px rgba(57, 255, 20, 0.1);
        }
        
        .purchase-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: var(--radius-sm);
            border: 1px solid var(--border-color);
        }
        
        .purchase-details h3 {
            font-size: 1.2rem;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }
        
        .purchase-details p {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        
        .purchase-actions {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
            justify-content: center;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: var(--bg-primary);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 1.5rem;
            text-align: center;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--neon-green);
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        
        @media (max-width: 768px) {
            .profile-container {
                grid-template-columns: 1fr;
            }
            
            .profile-sidebar {
                position: relative;
                top: 0;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .purchase-item {
                grid-template-columns: 80px 1fr;
            }
            
            .purchase-image {
                width: 80px;
                height: 80px;
            }
            
            .purchase-actions {
                grid-column: 2;
                flex-direction: row;
                margin-top: 1rem;
            }
        }
    </style>
</head>
<body>
    <?php include VIEWS_PATH . '/includes/header.php'; ?>
    
    <div class="container">
        <div class="profile-container">
            <!-- Sidebar -->
            <div class="profile-sidebar">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($userData['full_name'] ?? $userData['username'], 0, 1)); ?>
                </div>
                <h2 class="profile-name"><?php echo htmlspecialchars($userData['full_name'] ?? $userData['username']); ?></h2>
                <p class="profile-email"><?php echo htmlspecialchars($userData['email']); ?></p>
                
                <nav class="profile-nav">
                    <a class="profile-nav-item active" data-section="overview">
                        <i class="fas fa-chart-line"></i> Overview
                    </a>
                    <a class="profile-nav-item" data-section="profile">
                        <i class="fas fa-user"></i> Edit Profile
                    </a>
                    <a class="profile-nav-item" data-section="password">
                        <i class="fas fa-lock"></i> Change Password
                    </a>
                    <a class="profile-nav-item" data-section="purchases">
                        <i class="fas fa-shopping-bag"></i> My Purchases
                    </a>
                </nav>
            </div>
            
            <!-- Content -->
            <div class="profile-content">
                <!-- Overview Section -->
                <div class="profile-section active" id="overview">
                    <h2>Account Overview</h2>
                    
                    <div class="stats-grid">
                        <div class="stat-box">
                            <div class="stat-value"><?php echo count($purchases); ?></div>
                            <div class="stat-label">Total Purchases</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">
                                <?php 
                                $totalSpent = array_sum(array_column($purchases, 'total_amount'));
                                echo Utils::formatCurrency($totalSpent);
                                ?>
                            </div>
                            <div class="stat-label">Total Spent</div>
                        </div>
                        <div class="stat-box">
                            <div class="stat-value">
                                <?php 
                                $completedPurchases = array_filter($purchases, function($p) {
                                    return $p['payment_status'] === 'completed';
                                });
                                echo count($completedPurchases);
                                ?>
                            </div>
                            <div class="stat-label">Completed Orders</div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem;">
                        <h3 style="color: var(--text-primary); margin-bottom: 1rem;">Account Information</h3>
                        <div style="display: grid; gap: 1rem;">
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: var(--bg-primary); border-radius: var(--radius-sm);">
                                <span style="color: var(--text-muted);">Username:</span>
                                <span style="color: var(--text-primary); font-weight: 600;"><?php echo htmlspecialchars($userData['username']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: var(--bg-primary); border-radius: var(--radius-sm);">
                                <span style="color: var(--text-muted);">Email:</span>
                                <span style="color: var(--text-primary); font-weight: 600;"><?php echo htmlspecialchars($userData['email']); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: var(--bg-primary); border-radius: var(--radius-sm);">
                                <span style="color: var(--text-muted);">Phone:</span>
                                <span style="color: var(--text-primary); font-weight: 600;"><?php echo htmlspecialchars($userData['phone'] ?? 'Not set'); ?></span>
                            </div>
                            <div style="display: flex; justify-content: space-between; padding: 1rem; background: var(--bg-primary); border-radius: var(--radius-sm);">
                                <span style="color: var(--text-muted);">Member Since:</span>
                                <span style="color: var(--text-primary); font-weight: 600;"><?php echo date('F d, Y', strtotime($userData['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Edit Profile Section -->
                <div class="profile-section" id="profile">
                    <h2>Edit Profile</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" value="<?php echo htmlspecialchars($userData['username']); ?>" disabled>
                            <small style="color: var(--text-muted);">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" value="<?php echo htmlspecialchars($userData['email']); ?>" disabled>
                            <small style="color: var(--text-muted);">Email cannot be changed</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars($userData['full_name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Phone</label>
                            <input type="tel" name="phone" value="<?php echo htmlspecialchars($userData['phone'] ?? ''); ?>">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </form>
                </div>
                
                <!-- Change Password Section -->
                <div class="profile-section" id="password">
                    <h2>Change Password</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="change_password">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password" required>
                        </div>
                        
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password" required minlength="6">
                            <small style="color: var(--text-muted);">Minimum 6 characters</small>
                        </div>
                        
                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="confirm_password" required minlength="6">
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-lock"></i> Change Password
                        </button>
                    </form>
                </div>
                
                <!-- Purchases Section -->
                <div class="profile-section" id="purchases">
                    <h2>My Purchases</h2>
                    
                    <?php if (empty($purchases)): ?>
                        <div style="text-align: center; padding: 3rem;">
                            <i class="fas fa-shopping-bag" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 1rem;"></i>
                            <h3 style="color: var(--text-primary); margin-bottom: 0.5rem;">No purchases yet</h3>
                            <p style="color: var(--text-muted); margin-bottom: 2rem;">Start shopping to see your purchases here</p>
                            <a href="<?php echo APP_URL; ?>/products.php" class="btn btn-primary">
                                <i class="fas fa-shopping-bag"></i> Browse Products
                            </a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($purchases as $purchase): ?>
                        <div class="purchase-item">
                            <img src="<?php echo $purchase['thumbnail_image'] ? APP_URL . '/uploads/products/' . $purchase['thumbnail_image'] : IMG_URL . '/placeholder.jpg'; ?>" 
                                 alt="<?php echo htmlspecialchars($purchase['product_name']); ?>"
                                 class="purchase-image">
                            
                            <div class="purchase-details">
                                <h3><?php echo htmlspecialchars($purchase['product_name']); ?></h3>
                                <p><i class="fas fa-calendar"></i> <?php echo date('F d, Y', strtotime($purchase['created_at'])); ?></p>
                                <p><i class="fas fa-rupee-sign"></i> <?php echo Utils::formatCurrency($purchase['total_amount']); ?></p>
                                <p>
                                    <i class="fas fa-circle" style="color: <?php echo $purchase['payment_status'] === 'completed' ? 'var(--neon-green)' : '#ff3232'; ?>; font-size: 0.5rem;"></i>
                                    <?php echo ucfirst($purchase['payment_status']); ?>
                                </p>
                            </div>
                            
                            <div class="purchase-actions">
                                <?php if ($purchase['payment_status'] === 'completed' && $purchase['product_file_path']): ?>
                                    <a href="<?php echo APP_URL; ?>/download.php?transaction_id=<?php echo $purchase['transaction_id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i> Download
                                    </a>
                                <?php endif; ?>
                                <a href="<?php echo APP_URL; ?>/product.php?slug=<?php echo $purchase['product_slug']; ?>" 
                                   class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View Product
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <?php include VIEWS_PATH . '/includes/footer.php'; ?>
    
    <script>
        // Profile navigation
        document.querySelectorAll('.profile-nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Remove active class from all items
                document.querySelectorAll('.profile-nav-item').forEach(i => i.classList.remove('active'));
                document.querySelectorAll('.profile-section').forEach(s => s.classList.remove('active'));
                
                // Add active class to clicked item
                this.classList.add('active');
                
                // Show corresponding section
                const sectionId = this.getAttribute('data-section');
                document.getElementById(sectionId).classList.add('active');
            });
        });
    </script>
</body>
</html>
