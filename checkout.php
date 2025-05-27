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
    $error = "<p class='error-msg'>{$_GET['error']}</p>";
}

// Ersätt placeholders
$template = str_replace("{{cart-items}}", $cartItems, $template);
$template = str_replace("{{error-message}}", $error, $template);

// Skriv ut HTML
echo $template;

