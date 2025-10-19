<?php
/**
 * PRODIGI - Homepage
 * Main entry point
 */

define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

// Initialize classes
$product = new Product();
$category = new Category();
$admin = new Admin();

// Get featured products (only approved and active)
$featuredResult = $product->getAllProducts(['is_featured' => 1], 1, 8);
$featuredProducts = $featuredResult['products'];

// Get all active categories
$categories = $category->getAllCategories(true);

// Get stats
$stats = $admin->getDashboardStats();

// Include view
include VIEWS_PATH . '/home.php';
?>
