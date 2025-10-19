<?php
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../../config/config.php';

$db = Database::getInstance();

echo "Updating transactions table...\n\n";

try {
    // Add total_amount column
    echo "Adding total_amount column...\n";
    $db->query("ALTER TABLE transactions ADD COLUMN total_amount DECIMAL(10,2) DEFAULT NULL AFTER amount");
    echo "✓ Added total_amount column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ total_amount column already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    // Update existing records
    echo "Updating total_amount values...\n";
    $db->query("UPDATE transactions SET total_amount = amount WHERE total_amount IS NULL");
    echo "✓ Updated total_amount values\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Add created_at column
    echo "Adding created_at column...\n";
    $db->query("ALTER TABLE transactions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER transaction_date");
    echo "✓ Added created_at column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ created_at column already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    // Update existing records
    echo "Updating created_at values...\n";
    $db->query("UPDATE transactions SET created_at = transaction_date WHERE created_at IS NULL OR created_at < transaction_date");
    echo "✓ Updated created_at values\n";
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
}

try {
    // Add razorpay_order_id column
    echo "Adding razorpay_order_id column...\n";
    $db->query("ALTER TABLE transactions ADD COLUMN razorpay_order_id VARCHAR(100) DEFAULT NULL AFTER payment_id");
    echo "✓ Added razorpay_order_id column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ razorpay_order_id column already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    // Add razorpay_payment_id column
    echo "Adding razorpay_payment_id column...\n";
    $db->query("ALTER TABLE transactions ADD COLUMN razorpay_payment_id VARCHAR(100) DEFAULT NULL AFTER razorpay_order_id");
    echo "✓ Added razorpay_payment_id column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ razorpay_payment_id column already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    // Add razorpay_signature column
    echo "Adding razorpay_signature column...\n";
    $db->query("ALTER TABLE transactions ADD COLUMN razorpay_signature VARCHAR(255) DEFAULT NULL AFTER razorpay_payment_id");
    echo "✓ Added razorpay_signature column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ razorpay_signature column already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

try {
    // Add paid_at column
    echo "Adding paid_at column...\n";
    $db->query("ALTER TABLE transactions ADD COLUMN paid_at TIMESTAMP NULL AFTER payment_date");
    echo "✓ Added paid_at column\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column') !== false) {
        echo "✓ paid_at column already exists\n";
    } else {
        echo "✗ Error: " . $e->getMessage() . "\n";
    }
}

echo "\n✅ Migration completed successfully!\n";
