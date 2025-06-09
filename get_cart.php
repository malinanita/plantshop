<?php
// get_cart.php (9 - Gesällprov)
// Returnerar innehållet i kundvagnen som JSON

session_start();
header('Content-Type: application/json'); // Svar ges som JSON

// Hämta kundvagnen från sessionen, eller en tom array om den inte finns
$cart = $_SESSION['cart'] ?? [];

// Skicka kundvagnen som JSON med omindexerade värden
echo json_encode(["cart" => array_values($cart)]);
