<?php
require_once "db.php";
session_start();

// Ladda HTML-mall
$template = file_get_contents("login.html");

// Feedbackmeddelanden
$feedback = "";

// Registreringslycka
if (isset($_GET['registered']) && $_GET['registered'] === "success") {
    $feedback .= file_get_contents("templates/login_success_registration.html");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        $feedback .= file_get_contents("templates/login_error_missing_fields.html");
    } else {
        $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"] = $user["name"];
            $_SESSION["email"] = $user["email"];
            header("Location: profile.php");
            exit;
        } else {
            $feedback .= file_get_contents("templates/login_error_wrong_credentials.html");
        }
    }
}

// Ersätt placeholder med feedback
$template = str_replace("{{login-feedback}}", $feedback, $template);
echo $template;
