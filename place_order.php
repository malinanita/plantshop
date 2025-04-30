<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Du måste vara inloggad.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$cart = $data['cart'];
$name = $data['name'];
$address = $data['address'];
$email = $data['email'];
$userId = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // Räkna ut totalpris
    $total = 0;
    foreach ($cart as $item) {
        $total += $item['price'] * $item['quantity'];
    }

    // Skapa order
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price, delivery_name, delivery_address, delivery_email, created_at)
        VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->execute([$userId, $total, $name, $address, $email]);
    $orderId = $pdo->lastInsertId();

    // Skapa order_items
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price)
        VALUES (?, ?, ?, ?)");

    foreach ($cart as $item) {
        $stmtItem->execute([$orderId, $item['id'], $item['quantity'], $item['price']]);
    }

    $pdo->commit();

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['error' => 'Något gick fel: ' . $e->getMessage()]);
}
