<?php
session_start();
require_once "db.php"; // för framtida logik om du vill

$cart = $_SESSION['cart'] ?? [];
$cartHtml = "";
$total = 0;

if (empty($cart)) {
    $cartHtml = "<p>Kundvagnen är tom.</p>";
} else {
    foreach ($cart as $item) {
        $sum = $item['price'] * $item['quantity'];
        $total += $sum;

        $cartHtml .= <<<HTML
<article class="cart-item">
  <img src="{$item['image']}" alt="{$item['name']}">
  <h3>{$item['name']}</h3>
  <p>Pris: {$item['price']} kr</p>
  <p>Antal: {$item['quantity']}</p>
</article>
HTML;
    }

    $cartHtml .= "<h3>Total: {$total} kr</h3>";
}

// Läs in HTML-mallen
$template = file_get_contents("checkout.html");

// Ersätt {{cart-items}}
$template = str_replace("{{cart-items}}", $cartHtml, $template);

// Lägg till felmeddelanden vid behov
$errorMessage = "";
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'login':
            $errorMessage = "<p class='error-msg'>Du måste vara inloggad för att slutföra köpet.</p>";
            break;
        case 'missing':
            $errorMessage = "<p class='error-msg'>Vänligen fyll i alla fält.</p>";
            break;
        case 'empty':
            $errorMessage = "<p class='error-msg'>Din kundvagn är tom.</p>";
            break;
        case 'server':
            $errorMessage = "<p class='error-msg'>Ett tekniskt fel uppstod. Försök igen.</p>";
            break;
    }
}
$template = str_replace("{{error-message}}", $errorMessage, $template);

// Visa sidan
echo $template;
