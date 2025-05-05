<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if (!$email || !$password) {
    echo json_encode(['success' => false, 'message' => 'E-post och lösenord krävs.']);
    exit;
}

try {
    $stmt = $db->prepare("SELECT id, name, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(['success' => false, 'message' => 'Fel e-post eller lösenord.']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];

    echo json_encode(['success' => true, 'name' => $user['name'] ?? '']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Serverfel.']);
}
