<?php
require_once "db.php";

// Om formuläret skickas in
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Validera och hämta formulärdata
    if (!isset($_POST["name"], $_POST["email"], $_POST["password"])) {
        header("Location: register.php?error=missing_fields");
        exit;
    }

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Kontrollera om e-post redan finns
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: register.php?error=email_taken");
        exit;
    }

    // Hasha lösenordet
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Spara ny användare
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $hashedPassword])) {
        header("Location: login.php?registered=success");
        exit;
    } else {
        header("Location: register.php?error=registration_failed");
        exit;
    }
}

// Om vi hamnar här via GET (för att visa formuläret med ev. fel)
$template = file_get_contents("register.html");

$feedback = "";
$loginLink = "<p>Har du redan ett konto? <a href='login.php'>Logga in här</a>.</p>";

if (isset($_GET["error"])) {
    switch ($_GET["error"]) {
        case "missing_fields":
            $feedback = "<span class='error-msg'>Fyll i alla fält.</span>";
            break;
        case "email_taken":
            $feedback = "<span class='error-msg'>E-postadressen är redan registrerad.</span>";
            break;
        case "registration_failed":
            $feedback = "<span class='error-msg'>Registreringen misslyckades. Försök igen.</span>";
            break;
    }
}

$template = str_replace("{{feedback}}", $feedback, $template);
$template = str_replace("{{login-link}}", $loginLink, $template);

echo $template;