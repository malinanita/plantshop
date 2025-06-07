<?php
require_once "db.php";
session_start();

if (!isset($_GET['id'])) {
  header("Location: shop.php");
  exit;
}

$id = (int) $_GET['id'];
$stmt = $db->prepare("SELECT name, description, price, image_url FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

$template = file_get_contents("product.html");

if (!$product) {
  $productHtml = file_get_contents("templates/product_not_found.html");
} else {
  $productTemplate = file_get_contents("templates/product_detail.html");
  $productHtml = str_replace(
    ['{{id}}', '{{name}}', '{{description}}', '{{price}}', '{{image_url}}'],
    [$id, $product['name'], $product['description'], $product['price'], $product['image_url']],
    $productTemplate
  );
}

$template = str_replace("{{product-detail}}", $productHtml, $template);
echo $template;
