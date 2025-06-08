<?php
session_start();
require_once "db.php";

// Ta bort vara om det skickats in ett formulär
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $productId = $_POST['remove_item'];
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }
    header("Location: checkout.php");
    exit;
}

// Ladda mall
$template = file_get_contents("checkout.html");

// Kundvagnsinnehåll
$cartItems = "";
$total = 0;

$cart = $_SESSION["cart"] ?? [];
if (empty($cart)) {
    $cartItems = file_get_contents("templates/checkout_empty_cart.html");
} else {
    foreach ($cart as $id => $item) {
        $itemTemplate = file_get_contents("templates/checkout_item.html");
        $itemHtml = str_replace(
            ['{{name}}', '{{quantity}}', '{{price}}', '{{product_id}}'],
            [htmlspecialchars($item['name']), $item['quantity'], $item['price'], $id],
            $itemTemplate
        );
        $cartItems .= $itemHtml;
        $total += $item["price"] * $item["quantity"];
    }

    $totalHtml = file_get_contents("templates/checkout_total.html");
    $totalHtml = str_replace('{{total}}', $total, $totalHtml);
    $cartItems .= $totalHtml;
}

// Eventuella felmeddelanden
$error = "";
if (isset($_GET["error"])) {
    $errorType = trim($_GET["error"]);
    if ($errorType === "empty") {
        $error = file_get_contents("templates/checkout_error_empty.html");
    } elseif ($errorType === "login") {
        $error = file_get_contents("templates/checkout_error_login.html");
    } else {
        $error = file_get_contents("templates/checkout_error_server.html");
    }
}

// Ersätt placeholders
$template = str_replace(
    ["{{cart-items}}", "{{error-message}}"],
    [$cartItems, $error],
    $template
);

// Skriv ut HTML
echo $template;
