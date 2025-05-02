<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$id = (string)$data['id'];
$name = $data['name'];
$image = $data['image'];
$price = $data['price'];

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$found = false;
foreach ($_SESSION['cart'] as &$item) {
    if ((string)$item['id'] === $id) {
        $item['quantity'] += 1;
        $found = true;
        break;
    }
}
unset($item);

if (!$found) {
    $_SESSION['cart'][] = [
        'id' => $id,
        'name' => $name,
        'image' => $image,
        'price' => $price,
        'quantity' => 1
    ];
}

echo json_encode(['success' => true]);
