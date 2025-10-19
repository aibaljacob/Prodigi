<?php
/**
 * Reviews API
 * Handles adding, editing, and retrieving product reviews
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!User::isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Please login first']);
    exit;
}

$userId = $_SESSION['user_id'];
$db = Database::getInstance();

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? '';

try {
    switch ($action) {
        case 'add_review':
            $productId = (int)($data['product_id'] ?? 0);
            $rating = (int)($data['rating'] ?? 0);
            $reviewTitle = trim($data['review_title'] ?? '');
            $reviewText = trim($data['review_text'] ?? '');
            
            // Validate inputs
            if (!$productId || $rating < 1 || $rating > 5 || empty($reviewTitle) || empty($reviewText)) {
                echo json_encode(['success' => false, 'message' => 'Invalid input']);
                exit;
            }
            
            // Check if user has purchased this product
            $hasPurchased = $db->fetchOne(
                "SELECT t.transaction_id FROM transactions t
                 WHERE t.buyer_id = :user_id AND t.product_id = :product_id 
                 AND t.payment_status = 'completed'
                 LIMIT 1",
                ['user_id' => $userId, 'product_id' => $productId]
            );
            
            if (!$hasPurchased) {
                echo json_encode(['success' => false, 'message' => 'You must purchase this product to review it']);
                exit;
            }
            
            // Check if user has already reviewed this product
            $existingReview = $db->fetchOne(
                "SELECT review_id FROM reviews 
                 WHERE user_id = :user_id AND product_id = :product_id",
                ['user_id' => $userId, 'product_id' => $productId]
            );
            
            if ($existingReview) {
                echo json_encode(['success' => false, 'message' => 'You have already reviewed this product']);
                exit;
            }
            
            // Insert review
            $db->insert('reviews', [
                'product_id' => $productId,
                'user_id' => $userId,
                'transaction_id' => $hasPurchased['transaction_id'],
                'rating' => $rating,
                'review_title' => $reviewTitle,
                'review_text' => $reviewText,
                'is_verified_purchase' => 1,
                'is_approved' => 1 // Auto-approve for single vendor
            ]);
            
            // Update product rating average
            $avgRating = $db->fetchOne(
                "SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews 
                 FROM reviews 
                 WHERE product_id = :product_id AND is_approved = 1",
                ['product_id' => $productId]
            );
            
            $db->update('products', 
                [
                    'rating_average' => round($avgRating['avg_rating'], 1),
                    'total_reviews' => $avgRating['total_reviews']
                ],
                ['product_id' => $productId]
            );
            
            echo json_encode(['success' => true, 'message' => 'Review submitted successfully']);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    error_log("Reviews API Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'An error occurred']);
}
?>
