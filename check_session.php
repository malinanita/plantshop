<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

// Om sessionen redan finns
if (isset($_SESSION['user_id'])) {
    echo json_encode([
        'loggedIn' => true,
        'email' => $_SESSION['email'],
        'name' => $_SESSION['name']
    ]);
    exit;
}

// Om det finns en login-token-cookie
if (isset($_COOKIE['login_token'])) {
    $stmt = $pdo->prepare("SELECT id, email, name FROM users WHERE login_token = ?");
    $stmt->execute([$_COOKIE['login_token']]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['name'] = $user['name'];

        echo json_encode([
            'loggedIn' => true,
            'email' => $user['email'],
            'name' => $user['name']
        ]);
        exit;
    }
}

echo json_encode(['loggedIn' => false]);
