<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? (string)$data['id'] : null;

if (!$id || !isset($_SESSION['cart'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing ID or cart not initialized'
    ]);
    exit;
}

// Filtrera bort produkten
$_SESSION['cart'] = array_values(array_filter($_SESSION['cart'], function($item) use ($id) {
    return (string)$item['id'] !== $id;
}));

echo json_encode([
    'success' => true
]);
