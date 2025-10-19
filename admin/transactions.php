<?php
/**
 * Admin - Transactions Management
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

// Check if user is admin
if (!User::isAdmin()) {
    Utils::redirect(APP_URL . '/login.php', 'Access denied. Admin only.', 'error');
}

$admin = new Admin();
$stats = $admin->getDashboardStats();

// Get filters
$filters = [];
$whereConditions = ['1=1'];
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $whereConditions[] = '(buyer.username LIKE :search OR p.product_name LIKE :search OR t.transaction_id LIKE :search)';
    $params['search'] = '%' . $_GET['search'] . '%';
}

if (isset($_GET['status']) && !empty($_GET['status'])) {
    $whereConditions[] = 't.payment_status = :status';
    $params['status'] = $_GET['status'];
}

if (isset($_GET['from_date']) && !empty($_GET['from_date'])) {
    $whereConditions[] = 'DATE(t.transaction_date) >= :from_date';
    $params['from_date'] = $_GET['from_date'];
}

if (isset($_GET['to_date']) && !empty($_GET['to_date'])) {
    $whereConditions[] = 'DATE(t.transaction_date) <= :to_date';
    $params['to_date'] = $_GET['to_date'];
}

// Pagination
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 50;
$offset = ($page - 1) * $limit;

// Build query
$whereString = implode(' AND ', $whereConditions);

if (defined('SINGLE_VENDOR') && SINGLE_VENDOR) {
    $query = "SELECT t.*, 
                buyer.username as buyer_username,
                buyer.email as buyer_email,
                p.product_name
              FROM transactions t
              JOIN users buyer ON t.buyer_id = buyer.user_id
              JOIN products p ON t.product_id = p.product_id
              WHERE {$whereString}
              ORDER BY t.transaction_date DESC
              LIMIT :limit OFFSET :offset";
} else {
    $query = "SELECT t.*, 
                buyer.username as buyer_username,
                buyer.email as buyer_email,
                seller.username as seller_username,
                p.product_name
              FROM transactions t
              JOIN users buyer ON t.buyer_id = buyer.user_id
              JOIN users seller ON t.seller_id = seller.user_id
              JOIN products p ON t.product_id = p.product_id
              WHERE {$whereString}
              ORDER BY t.transaction_date DESC
              LIMIT :limit OFFSET :offset";
}

$params['limit'] = $limit;
$params['offset'] = $offset;

$db = Database::getInstance();
$transactions = $db->fetchAll($query, $params);

// Get total count
$countQuery = "SELECT COUNT(*) as total 
               FROM transactions t
               JOIN users buyer ON t.buyer_id = buyer.user_id
               JOIN products p ON t.product_id = p.product_id
               WHERE {$whereString}";
unset($params['limit'], $params['offset']);
$totalResult = $db->fetchOne($countQuery, $params);
$total = $totalResult['total'];
$totalPages = ceil($total / $limit);

// Calculate totals for current filter
$totalsQuery = "SELECT 
                  COUNT(*) as transaction_count,
                  SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_revenue
                FROM transactions t
                JOIN users buyer ON t.buyer_id = buyer.user_id
                JOIN products p ON t.product_id = p.product_id
                WHERE {$whereString}";
$totals = $db->fetchOne($totalsQuery, $params);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-body">
    <?php include '../views/admin/sidebar.php'; ?>
    
    <div class="admin-main">
        <header class="admin-header">
            <h1><i class="fas fa-receipt"></i> Transactions</h1>
            <div class="admin-user">
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-outline btn-sm">Logout</a>
            </div>
        </header>
        
        <div class="admin-content">
            <!-- Summary Stats -->
            <div class="stats-grid" style="grid-template-columns: repeat(2, 1fr); margin-bottom: 1.5rem;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #4B6EF5;">
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo number_format($totals['transaction_count']); ?></h3>
                        <p>Transactions (Filtered)</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #10B981;">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo Utils::formatCurrency($totals['total_revenue']); ?></h3>
                        <p>Revenue (Completed)</p>
                    </div>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-filter"></i> Filters</h2>
                </div>
                <div class="card-body">
                    <form method="GET" action="" class="filter-form">
                        <div class="filter-grid">
                            <input type="text" name="search" placeholder="Search by ID, buyer, product..." 
                                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>" class="filter-input">
                            
                            <select name="status" class="filter-input">
                                <option value="">All Status</option>
                                <option value="pending" <?php echo (isset($_GET['status']) && $_GET['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                                <option value="completed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'completed') ? 'selected' : ''; ?>>Completed</option>
                                <option value="failed" <?php echo (isset($_GET['status']) && $_GET['status'] === 'failed') ? 'selected' : ''; ?>>Failed</option>
                            </select>
                            
                            <input type="date" name="from_date" placeholder="From Date" 
                                   value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>" class="filter-input">
                            
                            <input type="date" name="to_date" placeholder="To Date" 
                                   value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>" class="filter-input">
                            
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-search"></i> Search
                            </button>
                            <a href="transactions.php" class="btn btn-outline btn-sm">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Transactions Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-list"></i> All Transactions (<?php echo number_format($total); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($transactions)): ?>
                        <p class="text-muted">No transactions found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th style="width: 60px;">S.No</th>
                                        <th>Date</th>
                                        <th>Buyer</th>
                                        <th>Product</th>
                                        <th>Amount</th>
                                        <th>Payment ID</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $serialNo = ($page - 1) * $limit + 1;
                                    foreach ($transactions as $txn): 
                                    ?>
                                    <tr>
                                        <td><strong><?php echo $serialNo++; ?></strong></td>
                                        <td>
                                            <?php echo date('M d, Y', strtotime($txn['transaction_date'])); ?><br>
                                            <small style="color: var(--text-secondary);">
                                                <?php echo date('H:i:s', strtotime($txn['transaction_date'])); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($txn['buyer_username']); ?></strong><br>
                                            <small style="color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($txn['buyer_email']); ?>
                                            </small>
                                        </td>
                                        <td><?php echo htmlspecialchars(Utils::truncate($txn['product_name'], 30)); ?></td>
                                        <td><strong><?php echo Utils::formatCurrency($txn['amount']); ?></strong></td>
                                        <td>
                                            <small style="color: var(--text-secondary);">
                                                <?php echo htmlspecialchars($txn['razorpay_payment_id'] ?? 'N/A'); ?>
                                            </small>
                                        </td>
                                        <td>
                                            <span class="badge badge-<?php echo $txn['payment_status']; ?>">
                                                <?php echo ucfirst($txn['payment_status']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>" 
                               class="btn btn-outline btn-sm">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                            <?php endif; ?>
                            
                            <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                            
                            <?php if ($page < $totalPages): ?>
                            <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>" 
                               class="btn btn-outline btn-sm">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
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
.filter-grid { 
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 0.75rem;
    align-items: center;
}
.filter-input { 
    padding: 0.5rem 0.75rem; 
    border: 1px solid var(--border-color); 
    border-radius: var(--radius-md); 
    background: var(--bg-secondary); 
    color: var(--text-primary);
}
.table-responsive { overflow-x: auto; }
.pagination {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
}
.page-info {
    color: var(--text-secondary);
    font-weight: 500;
}
</style>
