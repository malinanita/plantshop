<?php
require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST["name"], $_POST["email"], $_POST["password"])) {
        header("Location: register.php?error=missing_fields");
        exit;
    }

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: register.php?error=email_taken");
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $hashedPassword])) {
        header("Location: login.php?registered=success");
        exit;
    } else {
        header("Location: register.php?error=registration_failed");
        exit;
    }
}

// GET-förfrågan: visa formulär
$template = file_get_contents("register.html");

// Feedback
$feedback = "";
if (isset($_GET["error"])) {
    switch ($_GET["error"]) {
        case "email_taken":
            $feedback = file_get_contents("templates/register_error_email_taken.html");
            break;
        case "registration_failed":
            $feedback = file_get_contents("templates/register_error_failed.html");
            break;
    }
}

$loginLink = file_get_contents("templates/register_login_link.html");

$template = str_replace("{{feedback}}", $feedback, $template);
$template = str_replace("{{login-link}}", $loginLink, $template);

echo $template;
