<?php
require_once "db.php";
session_start();

// Ladda HTML-mall
$template = file_get_contents("login.html");

// Feedbackmeddelanden
$feedback = "";

// Visa lyckad registreringsfeedback
if (isset($_GET['registered']) && $_GET['registered'] === "success") {
    $feedback .= "<p class='success-msg'>Registrering lyckades! Du kan nu logga in.</p>";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if (!$email || !$password) {
        $feedback .= "<p class='error-msg'>Fyll i både e-post och lösenord.</p>";
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
            $feedback .= "<p class='error-msg'>Fel e-post eller lösenord.</p>";
        }
    }
}

// Ersätt placeholder med feedbackmeddelande
$template = str_replace("{{login-feedback}}", $feedback, $template);
echo $template;
