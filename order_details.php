<?php
session_start();
require 'db.php';
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Ej inloggad']);
    exit;
}

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo json_encode(['success' => false, 'message' => 'order_id saknas']);
    exit;
}

try {
    // Kontrollera att ordern tillhör användaren
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        throw new Exception("Ingen sådan order hittades.");
    }

    // Hämta produkter i ordern
    $itemStmt = $db->prepare("SELECT oi.quantity, oi.price_at_purchase, p.name
                              FROM order_items oi
                              JOIN products p ON oi.product_id = p.id
                              WHERE oi.order_id = ?");
    $itemStmt->execute([$order_id]);
    $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'order' => $order, 'items' => $items]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
