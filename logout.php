<?php
// logout.php (9 - Gesällprov)
// Loggar ut användaren genom att rensa session och cookie, och skickar tillbaka till login-sidan

session_start();

// Rensa all data i sessionen
$_SESSION = [];
session_unset();
session_destroy();

// Ta även bort sessionscookie om den används
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Skicka tillbaka användaren till inloggningssidan
header("Location: login.php");
exit();




