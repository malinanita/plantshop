<?php
// register.php (9 - Gesällprov)
// Visar registreringsformulär och hanterar nyregistrering av användare
// Validerar indata, kontrollerar att e-post är unik, sparar användare och ger feedback

require_once "db.php";

// Om formuläret skickats via POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Kontrollera att alla fält är ifyllda
    if (!isset($_POST["name"], $_POST["email"], $_POST["password"])) {
        header("Location: register.php?error=missing_fields");
        exit;
    }

    // Hämta och sanera indata
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Kontrollera om e-posten redan finns i databasen
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        header("Location: register.php?error=email_taken");
        exit;
    }

    // Hasha lösenordet innan det sparas
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Spara ny användare i databasen
    $stmt = $db->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $hashedPassword])) {
        // Skicka vidare till login med registreringsfeedback
        header("Location: login.php?registered=success");
        exit;
    } else {
        // Om registreringen misslyckades
        header("Location: register.php?error=registration_failed");
        exit;
    }
}

// Om det inte är en POST – visa registreringsformuläret
$template = file_get_contents("register.html");

// Visa ev. felmeddelande baserat på GET-parameter
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

// Lägg till länk till inloggning
$loginLink = file_get_contents("templates/register_login_link.html");

// Ersätt platsmarkörer i mallen
$template = str_replace("{{feedback}}", $feedback, $template);
$template = str_replace("{{login-link}}", $loginLink, $template);

// Skriv ut färdig sida
echo $template;
