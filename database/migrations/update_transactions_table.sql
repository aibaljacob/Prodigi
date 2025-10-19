-- Update transactions table to match application expectations
-- Add missing columns and aliases

-- Add total_amount as alias for amount
ALTER TABLE transactions ADD COLUMN total_amount DECIMAL(10,2) DEFAULT NULL AFTER amount;

-- Update existing records
UPDATE transactions SET total_amount = amount WHERE total_amount IS NULL;

-- Add created_at as alias for transaction_date  
ALTER TABLE transactions ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER transaction_date;

-- Update existing records
UPDATE transactions SET created_at = transaction_date WHERE created_at IS NULL OR created_at < transaction_date;

-- Add Razorpay specific columns if they don't exist
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS razorpay_order_id VARCHAR(100) DEFAULT NULL AFTER payment_id;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS razorpay_payment_id VARCHAR(100) DEFAULT NULL AFTER razorpay_order_id;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS razorpay_signature VARCHAR(255) DEFAULT NULL AFTER razorpay_payment_id;
ALTER TABLE transactions ADD COLUMN IF NOT EXISTS paid_at TIMESTAMP NULL AFTER payment_date;

-- Add indexes for Razorpay columns
ALTER TABLE transactions ADD INDEX IF NOT EXISTS idx_razorpay_order (razorpay_order_id);
ALTER TABLE transactions ADD INDEX IF NOT EXISTS idx_razorpay_payment (razorpay_payment_id);
