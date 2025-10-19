<?php
/**
 * API: Store Approval
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!User::isAdmin()) {
    Utils::jsonResponse(['success' => false, 'message' => 'Access denied'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Utils::jsonResponse(['success' => false, 'message' => 'Invalid request method'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$storeId = $input['store_id'] ?? null;
$action = $input['action'] ?? null;

if (!$storeId || !$action) {
    Utils::jsonResponse(['success' => false, 'message' => 'Store ID and action required'], 400);
}

$store = new Store();

if ($action === 'approve') {
    $result = $store->approveStore($storeId);
} elseif ($action === 'reject') {
    $result = $store->rejectStore($storeId);
} else {
    Utils::jsonResponse(['success' => false, 'message' => 'Invalid action'], 400);
}

Utils::jsonResponse($result);
?>
