<?php
/**
 * User Registration Page
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Redirect if already logged in
if (User::isLoggedIn()) {
    Utils::redirect(APP_URL);
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (Utils::validateCSRF($_POST['csrf_token'])) {
        // Basic server-side guards
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    // Collapse multiple spaces to a single space
    $fullName = preg_replace('/\s+/', ' ', $fullName);
        $password = $_POST['password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        // Reject spaces-only or weird repeated characters
        $isSpacesOnly = fn($v) => $v !== '' && strlen(trim($v)) === 0;
        $isWeirdRepeat = fn($v) => preg_match('/^(.)\1{2,}$/', $v); // same char 3+ times

        if ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif ($isSpacesOnly($username) || $isSpacesOnly($email) || $isSpacesOnly($fullName)) {
            $error = 'Inputs cannot be only spaces.';
        } elseif ($isWeirdRepeat($username) || $isWeirdRepeat($fullName)) {
            $error = 'Please avoid using the same character repeatedly.';
        } elseif (!preg_match('/^[A-Za-z ]{2,}$/', $fullName)) {
            $error = 'Full name can only contain letters and spaces.';
        } elseif (preg_match('/\s/', $password)) {
            $error = 'Password cannot contain spaces.';
        } elseif (strlen($password) < 8
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/[A-Z]/', $password)
            || !preg_match('/\d/', $password)
            || !preg_match('/[^A-Za-z0-9]/', $password)
        ) {
            $error = 'Password must be 8+ chars with upper, lower, number, and special, and no spaces.';
        } else {
            $user = new User();
            $result = $user->register([
                'username' => Utils::sanitize($username),
                'email' => Utils::sanitize($email),
                'password' => $password,
                'full_name' => Utils::sanitize($fullName),
                'phone' => ''
            ]);
        
            if ($result['success']) {
                Utils::redirect('login.php', 'Registration successful! Please login.', 'success');
            } else {
                $error = $result['message'];
            }
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
    <title>Register - PRODIGI</title>
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
                <h2>Create Your Account</h2>
                <p>Join thousands of creators on PRODIGI</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="auth-form" id="registerForm" novalidate>
                <input type="hidden" name="csrf_token" value="<?php echo Utils::getCSRFToken(); ?>">
                
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="reg_username" name="username" required 
                           pattern="[a-zA-Z0-9_]{3,20}" 
                           title="3-20 characters, letters, numbers, and underscore only">
                    <small class="error-text" id="reg_username_error" style="display:none;"></small>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="reg_email" name="email" required>
                    <small class="error-text" id="reg_email_error" style="display:none;"></small>
                </div>
                
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="reg_fullname" name="full_name" required pattern="[A-Za-z ]{2,}" title="Only letters and spaces are allowed">
                    <small class="error-text" id="reg_fullname_error" style="display:none;"></small>
                </div>
                
                
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="reg_password" name="password" required minlength="8" pattern="[^\s]+" title="No spaces. 8+ chars with upper, lower, number, and special" autocomplete="new-password">
                    <small>No spaces. Min 8 chars incl. upper, lower, number, special</small>
                    <small class="error-text" id="reg_password_error" style="display:none;"></small>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <input type="password" id="reg_confirm" name="confirm_password" required minlength="8" pattern="[^\s]+" title="No spaces" autocomplete="new-password">
                    <small class="error-text" id="reg_confirm_error" style="display:none;"></small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block" id="reg_submit" disabled>
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
    <script src="<?php echo JS_URL; ?>/auth-validate.js"></script>
</body>
</html>
