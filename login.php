<?php
/**
 * User Login Page
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    if (User::isAdmin()) {
        Utils::redirect('admin/dashboard.php');
    } else {
        Utils::redirect('index.php');
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Utils::validateCSRF($_POST['csrf_token'])) {
        // Basic server-side guards
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($username === '' || strlen(trim($password)) === 0) {
            $error = 'Username and password are required.';
        } elseif (preg_match('/\s/', $password)) {
            $error = 'Password cannot contain spaces.';
        } else {
            $user = new User();
            $result = $user->login(
                Utils::sanitize($username),
                $password
            );
        }
        
        if ($result['success']) {
            // Redirect based on user type
            if ($result['user']['user_type'] === 'admin') {
                Utils::redirect('admin/dashboard.php', 'Welcome back, Admin!', 'success');
            } else {
                Utils::redirect('index.php', 'Welcome back!', 'success');
            }
        } else {
            $error = $result['message'];
        }
    } else {
        $error = 'Invalid CSRF token';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PRODIGI</title>
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/dark-neon-theme.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/auth-dark.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <h1><span class="gradient-text"><a href="index.php">PRODIGI</a></span></h1>
                <h2>Welcome Back</h2>
                <p>Login to continue to your account</p>
                <?php if (REQUIRE_EMAIL_VERIFICATION): ?>
                <p class="text-muted" style="margin-top:6px; font-size:14px;">Haven't verified yet? <a href="verify.php">Verify your email</a>.</p>
                <?php endif; ?>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form" id="loginForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo Utils::getCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="login_username">Username or Email</label>
                    <input type="text" id="login_username" name="username" required autofocus>
                    <small class="error-text" id="login_username_error" style="display:none;"></small>
                </div>
                
                <div class="form-group">
                    <label for="login_password">Password</label>
                    <input type="password" id="login_password" name="password" required pattern="[^\s]+" title="No spaces allowed" autocomplete="current-password">
                    <small class="error-text" id="login_password_error" style="display:none;"></small>
                </div>
                
                <div class="form-group-inline">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember"> Remember me
                    </label>
                    <a href="forgot-password.php" class="link-text">Forgot Password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" id="login_submit" disabled>
                    <i class="fas fa-sign-in-alt"></i> Login
                </button>
            </form>
            
            <div class="auth-divider">
                <span>or</span>
            </div>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
            </div>
            
            <div class="demo-credentials">
                <small><strong>Demo Admin:</strong> admin / admin123</small>
            </div>
        </div>
    </div>
    <script src="<?php echo JS_URL; ?>/auth-validate.js"></script>
</body>
</html>
