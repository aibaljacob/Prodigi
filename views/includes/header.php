<header>
    <nav class="navbar container">
        <a href="<?php echo APP_URL; ?>" class="logo">
            PRO<span>DIGI</span>
        </a>
        
    <ul class="nav-links nav-center" id="navMenu">
            <li><a href="<?php echo APP_URL; ?>">Home</a></li>
            <li><a href="<?php echo APP_URL; ?>/products.php">Products</a></li>
            <?php if (User::isLoggedIn()): ?>
                <?php if (User::isSeller()): ?>
                <!-- Single-vendor mode: no seller dashboard -->
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        
    <div class="nav-actions">
            <?php if (User::isLoggedIn()): ?>
                <?php if (User::isAdmin()): ?>
                    <a href="<?php echo APP_URL; ?>/admin/dashboard.php" class="btn btn-outline btn-sm">
                        <i class="fas fa-shield-alt"></i> Admin
                    </a>
                <?php endif; ?>
                <a href="<?php echo APP_URL; ?>/cart.php" class="cart-icon" title="Cart">
                    <i class="fas fa-shopping-cart"></i>
                    <?php
                    $cart = new Cart();
                    $cartCount = $cart->getCartCount(User::getCurrentUserId());
                    if ($cartCount > 0):
                    ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="<?php echo APP_URL; ?>/profile.php" class="icon-btn" title="Profile">
                    <i class="fas fa-user-circle"></i>
                </a>
                <a href="<?php echo APP_URL; ?>/logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            <?php else: ?>
                    <a href="<?php echo APP_URL; ?>/login.php" class="btn btn-outline btn-sm">Login</a>
                    <a href="<?php echo APP_URL; ?>/register.php" class="btn btn-secondary btn-sm">Sign Up</a>
            <?php endif; ?>
        </div>
        
        <button class="hamburger" id="hamburger" aria-label="Toggle menu" aria-expanded="false" aria-controls="navMenu">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>
</header>

<?php
// Display flash messages
$flash = Utils::getFlashMessage();
if ($flash):
?>
<div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMessage">
    <div class="container">
        <span><?php echo htmlspecialchars($flash['message']); ?></span>
        <button onclick="closeFlash()" class="close-flash">&times;</button>
    </div>
</div>
<style>
.flash-message {
    padding: 1rem;
    margin-bottom: 1rem;
    border-radius: var(--radius-md);
    display: flex;
    justify-content: space-between;
    align-items: center;
    animation: slideDown 0.3s ease;
}
.flash-success { background: #D1FAE5; color: #065F46; border-left: 4px solid #10B981; }
.flash-error { background: #FEE2E2; color: #991B1B; border-left: 4px solid #EF4444; }
.flash-info { background: #DBEAFE; color: #1E40AF; border-left: 4px solid #3B82F6; }
.close-flash {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    padding: 0 0.5rem;
}
@keyframes slideDown {
    from { transform: translateY(-100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
</style>
<script>
function closeFlash() {
    document.getElementById('flashMessage').style.display = 'none';
}
setTimeout(closeFlash, 5000);

// Mobile nav toggle
const hamburger = document.getElementById('hamburger');
const navMenu = document.getElementById('navMenu');
if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');
        hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
    // Close menu on link click (mobile)
    navMenu.querySelectorAll('a').forEach(a => a.addEventListener('click', () => {
        navMenu.classList.remove('open');
        hamburger.setAttribute('aria-expanded', 'false');
    }));
}
</script>
<?php endif; ?>
