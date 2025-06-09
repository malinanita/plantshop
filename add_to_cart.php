<?php
// add_to_cart.php (9 - Gesällprov)
// Tar emot produktdata via JSON och lägger till produkten i kundvagnen i sessionen

session_start();
header('Content-Type: application/json'); // Svar ges som JSON

// Hämta inkommande JSON-data från klienten
$data = json_decode(file_get_contents("php://input"), true);

// Extrahera och typomvandla produktinformation
$id = (string)$data['id'];
$name = $data['name'];
$image = $data['image'];
$price = $data['price'];

// Om kundvagn inte finns i session – skapa en tom
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;

// Gå igenom kundvagnen för att se om produkten redan finns
foreach ($_SESSION['cart'] as &$item) {
    if ((string)$item['id'] === $id) {
        $item['quantity'] += 1; // Öka kvantiteten om produkten finns
        $found = true;
        break;
    }
}
unset($item); // Avsluta referensvariabeln

// Om produkten inte hittades – lägg till ny post i kundvagnen
if (!$found) {
    $_SESSION['cart'][] = [
        'id' => $id,
        'name' => $name,
        'image' => $image,
        'price' => $price,
        'quantity' => 1
    ];
}

// Skicka JSON-svar till klienten som bekräftar att det lyckades
echo json_encode(['success' => true]);
