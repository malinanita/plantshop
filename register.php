<?php
require_once "db.php";
session_start();

$template = file_get_contents("register.html");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$name || !$email || !$password) {
        $feedback = "<span class='error-msg'>Fyll i alla fält.</span>";
    } else {
        $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $feedback = "<span class='error-msg'>E-postadressen är redan registrerad.</span>";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$name, $email, $hashedPassword]);
            $feedback = "<span class='success-msg'>Registrering lyckades! Du kan nu logga in.</span>";
        }
    }
} else {
    $feedback = "";
}

$template = str_replace("{{feedback}}", $feedback, $template);
echo $template;
