-- Migration: Add missing product columns
-- Date: 2025-10-19
-- Description: Add short_description and product file columns to products table

USE prodigi_db;

-- Add short_description column
ALTER TABLE products 
ADD COLUMN short_description VARCHAR(255) DEFAULT NULL AFTER product_description;

-- Add product file columns
ALTER TABLE products 
ADD COLUMN product_file_path VARCHAR(255) DEFAULT NULL AFTER thumbnail_image,
ADD COLUMN product_file_original_name VARCHAR(255) DEFAULT NULL AFTER product_file_path,
ADD COLUMN product_file_size_bytes BIGINT DEFAULT NULL AFTER product_file_original_name;

-- Add index for better performance
ALTER TABLE products 
ADD INDEX idx_product_file (product_file_path);

-- Update file_size_mb based on product_file_size_bytes for existing records
UPDATE products 
SET file_size_mb = ROUND(product_file_size_bytes / 1048576, 2)
WHERE product_file_size_bytes IS NOT NULL;
