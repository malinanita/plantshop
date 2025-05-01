<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$id = $data['id'];

$_SESSION['cart'] = array_filter($_SESSION['cart'] ?? [], function($item) use ($id) {
    return $item['id'] !== $id;
});

echo json_encode(['success' => true]);
