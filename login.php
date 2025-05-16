<?php
session_start();
require_once "db.php";

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Hämta användare
$stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifiera lösenord
if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['email'] = $user['email'];

    header("Location: profile.php");
    exit;
} else {
    header("Location: profile.php?error=invalid");
    exit;
}
