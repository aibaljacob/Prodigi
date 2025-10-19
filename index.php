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

// Get featured products
$featuredResult = $product->getAllProducts(['is_featured' => 1], 1, 8);
$featuredProducts = $featuredResult['products'];

// Get all categories
$categories = $category->getAllCategories();

// Get stats
$stats = $admin->getDashboardStats();

// Include view
include VIEWS_PATH . '/home.php';
?>
