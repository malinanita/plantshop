<?php
session_start();
require 'db.php';

// Rensa token i databasen om användaren finns
if (isset($_SESSION['user_id'])) {
    $stmt = $db->prepare("UPDATE users SET login_token = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

// Rensa session + cookie
session_destroy();
setcookie('login_token', '', time() - 3600, "/");
http_response_code(200);
