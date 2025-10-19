<?php
/**
 * Logout
 */
define('PRODIGI_ACCESS', true);
require_once __DIR__ . '/config/config.php';

User::logout();
?>
