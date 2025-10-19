<?php
/**
 * Admin - Users Management
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!User::isAdmin()) {
    Utils::redirect(APP_URL . '/login.php', 'Access denied. Admin only.', 'error');
}

$admin = new Admin();
$stats = $admin->getDashboardStats();

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    Utils::validateCSRF($_POST['csrf_token'] ?? '');
    
    if ($_POST['action'] === 'toggle_status' && isset($_POST['user_id'])) {
        $result = $admin->toggleUserStatus($_POST['user_id']);
        if ($result['success']) {
            Utils::setFlashMessage('User status updated successfully', 'success');
        } else {
            Utils::setFlashMessage($result['message'] ?? 'Failed to update user', 'error');
        }
        Utils::redirect(APP_URL . '/admin/users.php');
    }
}

// Get filters
$filters = [];
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['user_type']) && !empty($_GET['user_type'])) {
    $filters['user_type'] = $_GET['user_type'];
}
if (isset($_GET['status']) && !empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 20;

// Get users
$users = $admin->getAllUsers($filters, $page, $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include '../views/admin/sidebar.php'; ?>
    
    <div class="admin-main">
        <header class="admin-header">
            <h1><i class="fas fa-users"></i> Manage Users</h1>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </header>
        
        <?php Utils::displayFlashMessage(); ?>
        
        <div class="admin-content">
            <!-- Filters -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-filter"></i> Filters</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="filter-form">
                        <div class="filter-group">
                            <input type="text" name="search" placeholder="Search by name, email, username..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="filter-input">
                            
                            <select name="user_type" class="filter-input">
                                <option value="">All User Types</option>
                                <option value="buyer" <?php echo (isset($_GET['user_type']) && $_GET['user_type'] === 'buyer') ? 'selected' : ''; ?>>Buyers</option>
                            </select>
                            
                            <select name="status" class="filter-input">
                                <option value="">All Status</option>
                                <option value="active" <?php echo (isset($_GET['status']) && $_GET['status'] === 'active') ? 'selected' : ''; ?>>Active</option>
                                <option value="inactive" <?php echo (isset($_GET['status']) && $_GET['status'] === 'inactive') ? 'selected' : ''; ?>>Inactive</option>
                            </select>
                            
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="users.php" class="btn btn-outline btn-sm">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Users Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> Users (<?php echo count($users); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($users)): ?>
                        <p class="text-muted">No users found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Email</th>
                                        <th>Type</th>
                                        <th>Joined</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($users as $user): ?>
                                    <tr>
                                        <td>#<?php echo $user['user_id']; ?></td>
                                        <td>
                                            <div class="user-info">
                                                <strong><?php echo htmlspecialchars($user['username']); ?></strong>
                                                <small><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <span class="badge badge-<?php echo $user['user_type']; ?>">
                                                <?php echo ucfirst($user['user_type']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo Utils::timeAgo($user['created_at']); ?></td>
                                        <td>
                                            <?php if ($user['is_active']): ?>
                                                <span class="badge badge-completed"><i class="fas fa-check"></i> Active</span>
                                            <?php else: ?>
                                                <span class="badge badge-failed"><i class="fas fa-ban"></i> Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="toggle_status">
                                                <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                                <button type="submit" class="btn btn-sm <?php echo $user['is_active'] ? 'btn-danger' : 'btn-success'; ?>" 
                                                        onclick="return confirm('<?php echo $user['is_active'] ? 'Ban' : 'Activate'; ?> this user?')">
                                                    <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                                    <?php echo $user['is_active'] ? 'Ban' : 'Activate'; ?>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo JS_URL; ?>/admin.js"></script>
</body>
</html>

<style>
.filter-form { width: 100%; }
.filter-group { display: flex; gap: 0.75rem; flex-wrap: wrap; }
.filter-input { 
    flex: 1; 
    min-width: 200px;
    padding: 0.5rem 0.75rem; 
    border: 1px solid var(--border-color); 
    border-radius: var(--radius-md); 
    background: var(--bg-secondary); 
    color: var(--text-primary);
}
.user-info { display: flex; flex-direction: column; }
.user-info small { color: var(--text-secondary); font-size: 0.8rem; }
.table-responsive { overflow-x: auto; }
.badge-buyer { background: rgba(57, 255, 20, 0.15); color: var(--neon-green); }
.badge-admin { background: rgba(255, 77, 106, 0.15); color: #FF4D6A; }
</style>
