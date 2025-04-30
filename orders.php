<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Du måste vara inloggad.");
}

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

echo "<h1>Dina ordrar</h1>";
foreach ($orders as $order) {
    echo "<h3>Order #{$order['id']} – {$order['created_at']}</h3>";
    echo "<p>Totalt: {$order['total_price']} kr</p>";

    // Hämta produkter i denna order
    $stmtItems = $pdo->prepare("SELECT oi.*, p.name FROM order_items oi JOIN products p ON oi.product_id = p.id WHERE oi.order_id = ?");
    $stmtItems->execute([$order['id']]);
    $items = $stmtItems->fetchAll();

    echo "<ul>";
    foreach ($items as $item) {
        echo "<li>{$item['name']} – {$item['quantity']} st á {$item['price']} kr</li>";
    }
    echo "</ul>";
}
?>
