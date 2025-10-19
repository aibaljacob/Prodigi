<?php
/**
 * API: Cart Count
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if (!User::isLoggedIn()) {
    Utils::jsonResponse(['count' => 0]);
}

$cart = new Cart();
$count = $cart->getCartCount(User::getCurrentUserId());

Utils::jsonResponse(['count' => $count]);
?>
