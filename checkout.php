<?php
session_start();
require_once "db.php";

// Ladda mall
$template = file_get_contents("checkout.html");

// Kundvagnsinnehåll
$cartItems = "";
$total = 0;

if (!isset($_SESSION["cart"]) || count($_SESSION["cart"]) === 0) {
    $cartItems = "<p>Kundvagnen är tom.</p>";
} else {
    foreach ($_SESSION["cart"] as $item) {
        $total += $item["price"] * $item["quantity"];
        $cartItems .= "<section class='cart-item'>";
        $cartItems .= "<p>{$item['name']} ({$item['quantity']}st) - {$item['price']} kr/st</p>";
        $cartItems .= "</section>";
    }
    $cartItems .= "<p><strong>Totalt: {$total} kr</strong></p>";
}

// Eventuellt felmeddelande
$error = "";
if (isset($_GET["error"])) {
    $errorType = trim($_GET["error"]);
    if ($errorType === "empty") {
        $error = "<p class='error-msg'>Din kundvagn är tom. Vänligen lägg till produkter innan du slutför köpet 🌿</p>";
    } elseif ($errorType === "login") {
        $error = "<p class='error-msg'>Oj! Du måste vara inloggad för att slutföra ditt köp.</p>";
    } else {
        $error = "<p class='error-msg'>Ett fel inträffade. Försök igen.</p>";
    }
}
// Ersätt placeholders
$template = str_replace("{{cart-items}}", $cartItems, $template);
$template = str_replace("{{error-message}}", $error, $template);

// Skriv ut HTML
echo $template;

