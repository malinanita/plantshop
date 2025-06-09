<?php
// remove_from_cart.php (9 - Gesällprov)
// Tar emot ett produkt-ID via JSON och tar bort produkten från kundvagnen i sessionen

session_start();
header('Content-Type: application/json'); // Svar ges som JSON

// Hämta inkommande JSON-data
$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? (string)$data['id'] : null;

// Kontrollera att ID finns och att kundvagn är initierad
if (!$id || !isset($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing ID or cart not initialized'
    ]);
    exit;
}

// Filtrera bort produkten med angivet ID från kundvagnen
$_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($id) {
    return (string)$item['id'] !== $id;
}));

// Skicka svar att borttagningen lyckades
echo json_encode([
    'success' => true
]);
