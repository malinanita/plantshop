<?php
// checkout.php (9 - Gesällprov)
// Visar innehållet i kundvagnen och låter användaren ta bort varor.
// Om kundvagnen är tom eller om ett fel uppstår visas ett meddelande.

session_start();
require_once "db.php";

// Kontrollera om användaren har skickat ett formulär för att ta bort en vara
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_item'])) {
    $productId = $_POST['remove_item'];

    // Ta bort vald produkt från kundvagnen
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }

    // Ladda om sidan efter borttagning
    header("Location: checkout.php");
    exit;
}

// Ladda HTML-mallen för hela kassasidan
$template = file_get_contents("checkout.html");

// Förbered innehållet som ska visas i kundvagnen
$cartItems = "";
$total = 0;

$cart = $_SESSION["cart"] ?? [];

// Om kundvagnen är tom – visa särskild mall
if (empty($cart)) {
    $cartItems = file_get_contents("templates/checkout_empty_cart.html");
} else {
    // Gå igenom varje vara i kundvagnen
    foreach ($cart as $id => $item) {
        $itemTemplate = file_get_contents("templates/checkout_item.html");

        // Byt ut platsmarkörer mot produktens data
        $itemHtml = str_replace(
            ['{{name}}', '{{quantity}}', '{{price}}', '{{product_id}}'],
            [htmlspecialchars($item['name']), $item['quantity'], $item['price'], $id],
            $itemTemplate
        );

        $cartItems .= $itemHtml;

        // Lägg till varans totalsumma till slutbeloppet
        $total += $item["price"] * $item["quantity"];
    }

    // Lägg till rad med totalsumma
    $totalHtml = file_get_contents("templates/checkout_total.html");
    $totalHtml = str_replace('{{total}}', $total, $totalHtml);
    $cartItems .= $totalHtml;
}

// Kontrollera om ett felmeddelande ska visas
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

// Fyll i HTML-mallen med kundvagnsinnehåll och ev. felmeddelande
$template = str_replace(
    ["{{cart-items}}", "{{error-message}}"],
    [$cartItems, $error],
    $template
);

// Skriv ut färdig sida
echo $template;
