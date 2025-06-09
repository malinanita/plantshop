<?php
// login.php (9 - Gesällprov)
// Visar inloggningsformulär, hanterar inloggning och ger feedback vid fel eller lyckad registrering

require_once "db.php";
session_start();

// Ladda HTML-mallen för inloggningssidan
$template = file_get_contents("login.html");

// Förbered feedbackmeddelande (visas ovanför formuläret)
$feedback = "";

// Om användaren nyss registrerat sig – visa bekräftelsemeddelande
if (isset($_GET['registered']) && $_GET['registered'] === "success") {
    $feedback .= file_get_contents("templates/login_success_registration.html");
}

// Hantera inloggningsförsök
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? ""); // Sanera e-post
    $password = $_POST["password"] ?? ""; // Hämta lösenord

    // Hämta användare från databasen baserat på e-post
    $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifiera lösenordet
    if ($user && password_verify($password, $user["password"])) {
        // Spara användardata i sessionen
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["name"] = $user["name"];
        $_SESSION["email"] = $user["email"];

        // Skicka användaren till profilsidan
        header("Location: profile.php");
        exit;
    } else {
        // Visa felmeddelande om inloggningen misslyckas
        $feedback .= file_get_contents("templates/login_error_wrong_credentials.html");
    }
}

// Ersätt plats för feedback i mallen och skriv ut sidan
$template = str_replace("{{login-feedback}}", $feedback, $template);
echo $template;
