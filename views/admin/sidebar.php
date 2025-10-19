<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h2 class="gradient-text">PRODIGI</h2>
        <p class="sidebar-subtitle">Admin Panel</p>
    </div>
    
    <nav class="sidebar-nav">
        <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
        
        <a href="<?php echo APP_URL; ?>/admin/users.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'users.php' ? 'active' : ''; ?>">
            <i class="fas fa-users"></i>
            <span>Users</span>
            <?php if (isset($stats['total_users'])): ?>
            <span class="nav-badge"><?php echo $stats['total_users']; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?php echo APP_URL; ?>/admin/products.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : ''; ?>">
            <i class="fas fa-box"></i>
            <span>Products</span>
            <?php if (isset($stats['total_products'])): ?>
            <span class="nav-badge"><?php echo $stats['total_products']; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="<?php echo APP_URL; ?>/admin/categories.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'categories.php' ? 'active' : ''; ?>">
            <i class="fas fa-tags"></i>
            <span>Categories</span>
        </a>
        
        <a href="<?php echo APP_URL; ?>/admin/transactions.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'transactions.php' ? 'active' : ''; ?>">
            <i class="fas fa-receipt"></i>
            <span>Transactions</span>
        </a>
        
        <div class="sidebar-divider"></div>
        
        <a href="<?php echo APP_URL; ?>" class="nav-item" target="_blank">
            <i class="fas fa-external-link-alt"></i>
            <span>View Site</span>
        </a>
        
        <a href="<?php echo APP_URL; ?>/logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </nav>
</aside>

<style>
.admin-body {
    display: flex;
    min-height: 100vh;
    background: var(--bg-primary);
    color: var(--text-primary);
}

.admin-sidebar {
    width: 260px;
    background: var(--bg-card);
    border-right: 1px solid var(--border-color);
    position: fixed;
    left: 0;
    top: 0;
    bottom: 0;
    overflow-y: auto;
    z-index: 1000;
}

.sidebar-header {
    padding: 1.75rem 1.25rem;
    border-bottom: 1px solid var(--border-color);
    text-align: center;
}

.sidebar-header h2 { font-size: 1.5rem; margin-bottom: 0.25rem; }
.sidebar-subtitle { font-size: 0.85rem; color: var(--text-secondary); }

.sidebar-nav { padding: 0.75rem 0; }

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.9rem;
    padding: 0.75rem 1.25rem;
    color: var(--text-secondary);
    transition: var(--transition);
    position: relative;
}

.nav-item:hover,
.nav-item.active {
    color: var(--neon-green);
    text-shadow: var(--glow-sm);
    background: rgba(57, 255, 20, 0.05);
}

.nav-item.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: var(--neon-green);
    box-shadow: var(--glow-sm);
}

.nav-item i { width: 20px; text-align: center; }

.nav-badge {
    margin-left: auto;
    background: var(--neon-green);
    color: var(--bg-primary);
    font-size: 0.7rem;
    padding: 0.1rem 0.5rem;
    border-radius: 10px;
    font-weight: 700;
}

.nav-badge.badge-warning { background: var(--accent-amber); color: #111; }

.sidebar-divider { height: 1px; background: var(--border-color); margin: 0.75rem 0; }

.admin-main { margin-left: 260px; flex: 1; padding: 2rem; }

.admin-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    background: var(--bg-card);
    padding: 1rem 1.25rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
}

.admin-header h1 { font-size: 1.6rem; color: var(--text-primary); }
.admin-user { display: flex; align-items: center; gap: 0.75rem; }

.admin-content { display: flex; flex-direction: column; gap: 1.5rem; }

.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }

.stat-card {
    background: var(--bg-card);
    padding: 1rem;
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    display: flex;
    gap: 0.9rem;
    align-items: center;
}

.stat-icon {
    width: 56px; height: 56px; border-radius: var(--radius-md);
    display: flex; align-items: center; justify-content: center;
    color: #fff; font-size: 1.4rem;
}

.stat-info h3 { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.stat-info p { color: var(--text-secondary); font-size: 0.85rem; }

.dashboard-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(380px, 1fr)); gap: 1.25rem; }

.dashboard-card {
    background: var(--bg-card);
    border-radius: var(--radius-md);
    border: 1px solid var(--border-color);
    overflow: hidden;
}

.card-header { padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
.card-header h2 { font-size: 1.1rem; color: var(--text-primary); display: flex; align-items: center; gap: 0.5rem; }
.card-body { padding: 1.25rem; }

.data-table { width: 100%; border-collapse: collapse; }
.data-table th { text-align: left; padding: 0.65rem; border-bottom: 1px solid var(--border-color); font-weight: 600; color: var(--text-secondary); }
.data-table td { padding: 0.65rem; border-bottom: 1px solid var(--border-color); color: var(--text-primary); }

.badge { display: inline-block; padding: 0.2rem 0.6rem; border-radius: 999px; font-size: 0.8rem; font-weight: 700; }
.badge-pending { background: rgba(255,200,87,0.15); color: var(--accent-amber); }
.badge-completed { background: rgba(57,255,20,0.15); color: var(--neon-green); }
.badge-failed { background: rgba(255,77,106,0.15); color: #FF4D6A; }
.badge-approved { background: rgba(0,255,127,0.15); color: var(--neon-green-light); }
.badge-rejected { background: rgba(255,77,106,0.15); color: #FF4D6A; }

.text-muted { color: var(--text-secondary); text-align: center; padding: 2rem; }

.btn-sm { padding: 0.45rem 0.9rem; font-size: 0.85rem; }
.btn-success { background: #10B981; color: #0a0a0a; }
.btn-danger { background: #EF4444; color: #0a0a0a; }

@media (max-width: 768px) {
    .admin-sidebar { width: 0; transform: translateX(-100%); }
    .admin-sidebar.active { width: 260px; transform: translateX(0); }
    .admin-main { margin-left: 0; }
}
</style>
