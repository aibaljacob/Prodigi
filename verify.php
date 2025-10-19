<?php
/**
 * Email Verification Endpoint
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

$error = '';
$success = '';

$token = isset($_GET['token']) ? trim($_GET['token']) : (isset($_GET['t']) ? trim($_GET['t']) : '');

if ($token !== '') {
    try {
        $db = Database::getInstance();
        // Verify by token
        $updated = $db->update(
            'users',
            [
                'email_verified' => 1,
                'verification_token' => null,
            ],
            'verification_token = :token',
            ['token' => $token]
        );

        if ($updated > 0) {
            Utils::redirect('login.php', 'Email verified! You can login now.', 'success');
            exit;
        } else {
            $error = 'Invalid or expired verification link.';
        }
    } catch (Exception $e) {
        $error = 'Verification failed: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Verify Email - PRODIGI</title>
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
                <h2>Verify Your Email</h2>
                <p>Paste your verification link here if you have one.</p>
            </div>

            <?php if ($error): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="GET" action="" class="auth-form">
                <div class="form-group">
                    <label for="token">Verification Token</label>
                    <input type="text" id="token" name="token" placeholder="Paste token value from email link" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-check"></i> Verify Email
                </button>
            </form>

            <div class="auth-footer">
                <p>Already verified? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>
</body>
</html>
