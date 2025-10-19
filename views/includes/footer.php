<footer>
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3 class="gradient-text">PRODIGI</h3>
                <p style="color: var(--text-light); margin-top: 1rem;">
                    Curated digital downloads from the store owner. Premium templates, graphics, audio, video, ebooks, and more—instant access.
                </p>
                <div style="margin-top: 1rem; display: flex; gap: 1rem;">
                    <a href="#" class="icon-btn"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="icon-btn"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="icon-btn"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="icon-btn"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
            
            <div class="footer-section">
                <h3>Shop</h3>
                <ul>
                    <li><a href="<?php echo APP_URL; ?>/products.php">All Products</a></li>
                    <li><a href="<?php echo APP_URL; ?>/cart.php">Shopping Cart</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Account</h3>
                <ul>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="<?php echo APP_URL; ?>/profile.php">Your Profile</a></li>
                    <?php else: ?>
                        <li><a href="<?php echo APP_URL; ?>/login.php">Login</a></li>
                        <li><a href="<?php echo APP_URL; ?>/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> PRODIGI. All rights reserved. Instant downloads, secure checkout.</p>
        </div>
    </div>
</footer>
