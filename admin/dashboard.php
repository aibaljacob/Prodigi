<?php
/**
 * Admin Dashboard
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!User::isAdmin()) {
    Utils::redirect(APP_URL . '/login.php', 'Access denied. Admin only.', 'error');
}

$admin = new Admin();
$stats = $admin->getDashboardStats();
$pendingProducts = $admin->getPendingProductApprovals();
$recentTransactions = $admin->getAllTransactions(1, 10);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include '../views/admin/sidebar.php'; ?>
    
    <div class="admin-main">
        <header class="admin-header">
            <h1>Dashboard</h1>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </header>
        
        <div class="admin-content">
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4B6EF5;">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_users']); ?></h3>
                        <p>Total Users</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #F5B400;">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_products']); ?></h3>
                        <p>Total Products</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10B981;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo Utils::formatCurrency($stats['total_revenue']); ?></h3>
                        <p>Total Revenue</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #EF4444;">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($stats['total_transactions']); ?></h3>
                        <p>Total Sales</p>
                    </div>
                </div>
            </div>
            
            <!-- Pending Products (single-vendor) -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-box"></i> Pending Product Approvals</h2>
                    <span class="badge"><?php echo count($pendingProducts); ?></span>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingProducts)): ?>
                        <p class="text-muted">No pending product approvals</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product Name</th>
                                    <th>Price</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach (array_slice($pendingProducts, 0, 5) as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['product_name']); ?></td>
                                    <td><?php echo Utils::formatCurrency($product['price']); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-success" onclick="approveProduct(<?php echo $product['product_id']; ?>)">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Recent Transactions -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-receipt"></i> Recent Transactions</h2>
                </div>
                <div class="card-body">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Buyer</th>
                                <th>Product</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentTransactions as $transaction): ?>
                            <tr>
                                <td>#<?php echo $transaction['transaction_id']; ?></td>
                                <td><?php echo htmlspecialchars($transaction['buyer_username']); ?></td>
                                <td><?php echo htmlspecialchars(Utils::truncate($transaction['product_name'], 30)); ?></td>
                                <td><?php echo Utils::formatCurrency($transaction['amount']); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $transaction['payment_status']; ?>">
                                        <?php echo ucfirst($transaction['payment_status']); ?>
                                    </span>
                                </td>
                                <td><?php echo Utils::timeAgo($transaction['transaction_date']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    
    <script src="<?php echo JS_URL; ?>/admin.js"></script>
</body>
</html>
