<?php
header('Content-Type: application/json');
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $user['name'];

        // Skapa ett login-token
        $token = bin2hex(random_bytes(32));
        setcookie('login_token', $token, time() + (30 * 24 * 60 * 60), "/");

        $stmt = $pdo->prepare("UPDATE users SET login_token = ? WHERE id = ?");
        $stmt->execute([$token, $user['id']]);

        echo json_encode(['success' => true, 'email' => $email, 'name' => $user['name']]);
    } else {
        echo json_encode(['success' => false]);
    }
}
