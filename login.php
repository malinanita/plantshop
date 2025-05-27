<?php
require_once "db.php";
session_start();

$template = file_get_contents("login.html");

$feedback = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        $feedback = "<span class='error-msg'>Fyll i både e-post och lösenord.</span>";
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
            $feedback = "<span class='error-msg'>Fel e-post eller lösenord.</span>";
        }
    }
}

$template = str_replace("{{login-feedback}}", $feedback, $template);
echo $template;
