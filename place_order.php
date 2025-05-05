<?php
ob_start();
session_start();
require 'db.php';

header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Du måste vara inloggad för att beställa.");
    }

    if (!$data || !isset($data['name'], $data['address'], $data['email'])) {
        throw new Exception("Felaktig eller ofullständig data mottagen.");
    }

    $cart = $_SESSION['cart'] ?? [];
    if (empty($cart)) {
        throw new Exception("Din kundvagn är tom.");
    }

    // 🧮 Beräkna totalpris
    $total = 0;
    foreach ($cart as $item) {
        if (!isset($item['price'], $item['quantity'])) {
            throw new Exception("Kundvagnen innehåller ogiltiga värden.");
        }
        $total += $item['price'] * $item['quantity'];
    }

    $db->beginTransaction();

    // 📝 Spara order
    $stmt = $db->prepare("INSERT INTO orders (user_id, total_price, status, created_at) VALUES (?, ?, ?, NOW())");
    $stmt->execute([$_SESSION['user_id'], $total, 'bekräftad']);
    $order_id = $db->lastInsertId();

    // 🧾 Spara varje produkt i order_items
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, quantity, price_at_purchase) VALUES (?, ?, ?, ?)");

    foreach ($cart as $item) {
        $itemStmt->execute([
            $order_id,
            $item['id'],
            $item['quantity'],
            $item['price']
        ]);
    }

    $db->commit();
    $_SESSION['cart'] = [];

    ob_end_clean();
    echo json_encode(['success' => true, 'order_id' => $order_id]);
} catch (Exception $e) {
    if ($db && $db->inTransaction()) {
        $db->rollBack();
    }
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
